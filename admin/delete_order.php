<?php
require_once '../config/database.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id > 0) {
    $conn->begin_transaction();
    
    try {
        // Get order details first for logging/confirmation
        $order_query = "SELECT * FROM orders WHERE id = ?";
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $order = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if (!$order) {
            throw new Exception('Order not found.');
        }
        
        // If order is completed, restore stock to variants
        if ($order['status'] === 'completed' || $order['status'] === 'processing') {
            $items_query = "SELECT * FROM order_items WHERE order_id = ?";
            $stmt = $conn->prepare($items_query);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $items = $stmt->get_result();
            
            while ($item = $items->fetch_assoc()) {
                // Restore stock to variant
                $restore_stock = "UPDATE product_variants SET stock = stock + ? WHERE id = ?";
                $stmt2 = $conn->prepare($restore_stock);
                $stmt2->bind_param("ii", $item['quantity'], $item['variant_id']);
                $stmt2->execute();
                $stmt2->close();
                
                // Update product total stock
                $update_product = "UPDATE products p 
                                  SET stock = (SELECT SUM(stock) FROM product_variants WHERE product_id = p.id) 
                                  WHERE id = ?";
                $stmt3 = $conn->prepare($update_product);
                $stmt3->bind_param("i", $item['product_id']);
                $stmt3->execute();
                $stmt3->close();
            }
            $stmt->close();
        }
        
        // Delete order items first (foreign key constraint)
        $delete_items = "DELETE FROM order_items WHERE order_id = ?";
        $stmt = $conn->prepare($delete_items);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $stmt->close();
        
        // Delete the order
        $delete_order = "DELETE FROM orders WHERE id = ?";
        $stmt = $conn->prepare($delete_order);
        $stmt->bind_param("i", $order_id);
        
        if ($stmt->execute()) {
            $conn->commit();
            $_SESSION['success'] = 'Order #' . $order_id . ' has been deleted successfully!';
        } else {
            throw new Exception('Failed to delete order.');
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Failed to delete order: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid order ID.';
}

redirect('orders.php');
?>