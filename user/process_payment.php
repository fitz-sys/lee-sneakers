<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('cart.php');
}

$user_id = $_SESSION['user_id'];

// Get form data
$full_name = sanitize($_POST['full_name']);
$email = sanitize($_POST['email']);
$phone = sanitize($_POST['phone']);
$street = sanitize($_POST['street']);
$barangay = sanitize($_POST['barangay']);
$city = sanitize($_POST['city']);
$province = sanitize($_POST['province']);
$postal_code = sanitize($_POST['postal_code']);
$payment_method = sanitize($_POST['payment_method']);
$shipping_fee = floatval($_POST['shipping_fee']);

// Validate required fields
if (empty($full_name) || empty($email) || empty($phone) || empty($street) || 
    empty($barangay) || empty($city) || empty($province) || empty($postal_code)) {
    $_SESSION['error'] = 'Please fill in all required fields.';
    redirect('checkout.php');
}

// Handle GCash screenshot upload
$gcash_screenshot = null;
if ($payment_method === 'GCash') {
    // Check if file was uploaded
    if (!isset($_FILES['gcash_screenshot']) || $_FILES['gcash_screenshot']['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['error'] = 'Please upload your GCash payment screenshot for GCash orders.';
        redirect('checkout.php');
    }
    
    $file = $_FILES['gcash_screenshot'];
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    // Validate file type
    if (!in_array($file['type'], $allowed_types)) {
        $_SESSION['error'] = 'Invalid file type. Please upload JPG, PNG, or WEBP image.';
        redirect('checkout.php');
    }
    
    // Validate file size
    if ($file['size'] > $max_size) {
        $_SESSION['error'] = 'File size exceeds 5MB limit.';
        redirect('checkout.php');
    }
    
    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/gcash_payments/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename with proper extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $gcash_screenshot = 'gcash_' . $user_id . '_order_' . uniqid() . '_' . time() . '.' . $extension;
    $upload_path = $upload_dir . $gcash_screenshot;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        $_SESSION['error'] = 'Failed to upload payment screenshot. Please try again.';
        redirect('checkout.php');
    }
    
    // Verify file was uploaded successfully
    if (!file_exists($upload_path)) {
        $_SESSION['error'] = 'Screenshot upload verification failed. Please try again.';
        redirect('checkout.php');
    }
}

// Build shipping address
$shipping_address = json_encode([
    'full_name' => $full_name,
    'email' => $email,
    'phone' => $phone,
    'street' => $street,
    'barangay' => $barangay,
    'city' => $city,
    'province' => $province,
    'postal_code' => $postal_code
]);

$conn->begin_transaction();

try {
    // Get cart items
    $cart_query = "SELECT c.*, p.name, p.price, pv.stock, pv.id as variant_id
                   FROM cart c
                   JOIN products p ON c.product_id = p.id
                   JOIN product_variants pv ON c.variant_id = pv.id
                   WHERE c.user_id = ?";
    
    $stmt = $conn->prepare($cart_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $cart_items = $stmt->get_result();
    
    if ($cart_items->num_rows === 0) {
        throw new Exception('Cart is empty');
    }
    
    // Calculate total and validate stock
    $total_amount = $shipping_fee;
    $order_items = [];
    
    while ($item = $cart_items->fetch_assoc()) {
        // Check stock availability
        if ($item['stock'] < $item['quantity']) {
            throw new Exception('Insufficient stock for ' . $item['name']);
        }
        
        $item_total = $item['price'] * $item['quantity'];
        $total_amount += $item_total;
        $order_items[] = $item;
    }
    $stmt->close();
    
    // Create order with 'pending' status and gcash_screenshot
    $order_query = "INSERT INTO orders (user_id, total_amount, status, shipping_address, payment_method, gcash_screenshot, created_at) 
                    VALUES (?, ?, 'pending', ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("idsss", $user_id, $total_amount, $shipping_address, $payment_method, $gcash_screenshot);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order');
    }
    
    $order_id = $conn->insert_id;
    $stmt->close();
    
    // Insert order items and update stock
    foreach ($order_items as $item) {
        // Insert order item
        $item_query = "INSERT INTO order_items (order_id, product_id, variant_id, size, quantity, price) 
                       VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($item_query);
        $stmt->bind_param("iiisid", $order_id, $item['product_id'], $item['variant_id'], 
                         $item['size'], $item['quantity'], $item['price']);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert order item');
        }
        $stmt->close();
        
        // Update variant stock
        $new_stock = $item['stock'] - $item['quantity'];
        $update_stock = "UPDATE product_variants SET stock = ? WHERE id = ?";
        $stmt = $conn->prepare($update_stock);
        $stmt->bind_param("ii", $new_stock, $item['variant_id']);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update stock');
        }
        $stmt->close();
        
        // Update product total stock
        $update_product_stock = "UPDATE products p 
                                SET stock = (SELECT SUM(stock) FROM product_variants WHERE product_id = p.id) 
                                WHERE id = ?";
        $stmt = $conn->prepare($update_product_stock);
        $stmt->bind_param("i", $item['product_id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Clear cart
    $clear_cart = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($clear_cart);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    $conn->commit();
    
    // Redirect to success page
    $_SESSION['success'] = 'Order placed successfully!';
    redirect('order_success.php?order_id=' . $order_id);
    
} catch (Exception $e) {
    $conn->rollback();
    
    // Delete uploaded screenshot if order failed
    if ($gcash_screenshot && file_exists($upload_dir . $gcash_screenshot)) {
        unlink($upload_dir . $gcash_screenshot);
    }
    
    $_SESSION['error'] = 'Order failed: ' . $e->getMessage();
    redirect('checkout.php');
}
?>