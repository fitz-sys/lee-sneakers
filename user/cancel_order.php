<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('../index.php');
}

// Redirect admins to admin panel
if (isAdmin()) {
    redirect('../admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('profile.php');
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

if ($order_id <= 0) {
    $_SESSION['error'] = 'Invalid order ID.';
    redirect('profile.php');
}

// Verify order belongs to user and can be cancelled
$verify_query = "SELECT * FROM orders WHERE id = ? AND user_id = ? AND status IN ('pending', 'confirmed')";
$stmt = $conn->prepare($verify_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    $_SESSION['error'] = 'Order not found or cannot be cancelled.';
    redirect('profile.php');
}

$conn->begin_transaction();

try {
    // Get order items to restore stock
    $items_query = "SELECT product_id, variant_id, quantity FROM order_items WHERE order_id = ?";
    $stmt = $conn->prepare($items_query);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $order_items = $stmt->get_result();
    $stmt->close();
    
    // Restore stock for each item
    while ($item = $order_items->fetch_assoc()) {
        // Update variant stock
        $update_variant = "UPDATE product_variants SET stock = stock + ? WHERE id = ?";
        $stmt = $conn->prepare($update_variant);
        $stmt->bind_param("ii", $item['quantity'], $item['variant_id']);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to restore variant stock');
        }
        $stmt->close();
        
        // Update product total stock
        $update_product = "UPDATE products p 
                          SET stock = (SELECT SUM(stock) FROM product_variants WHERE product_id = p.id) 
                          WHERE id = ?";
        $stmt = $conn->prepare($update_product);
        $stmt->bind_param("i", $item['product_id']);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to restore product stock');
        }
        $stmt->close();
    }
    
    // Update order status to cancelled
    $cancel_query = "UPDATE orders SET status = 'cancelled', updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($cancel_query);
    $stmt->bind_param("i", $order_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to cancel order');
    }
    $stmt->close();
    
    $conn->commit();
    
    $_SESSION['success'] = 'Order #' . $order_id . ' has been cancelled successfully. Stock has been restored.';
    redirect('profile.php');
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = 'Failed to cancel order: ' . $e->getMessage();
    redirect('profile.php');
}
?>