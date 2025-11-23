<?php
session_start();
require_once '../config/database.php';

// Set JSON response header
header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => false,
        'message' => 'Please login to add items to cart'
    ]);
    exit;
}

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input data
if (!$data || !isset($data['product_id']) || !isset($data['variant_id']) || !isset($data['size']) || !isset($data['quantity'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request data'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($data['product_id']);
$variant_id = intval($data['variant_id']);
$size = sanitize($data['size']);
$quantity = intval($data['quantity']);

// Validate quantity
if ($quantity <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid quantity'
    ]);
    exit;
}

try {
    // Verify variant exists and has enough stock
    $variant_check = "SELECT pv.stock, p.name, p.price 
                     FROM product_variants pv 
                     JOIN products p ON pv.product_id = p.id 
                     WHERE pv.id = ? AND pv.product_id = ?";
    
    $stmt = $conn->prepare($variant_check);
    $stmt->bind_param("ii", $variant_id, $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $variant = $result->fetch_assoc();
    $stmt->close();
    
    if (!$variant) {
        echo json_encode([
            'success' => false,
            'message' => 'Product variant not found'
        ]);
        exit;
    }
    
    if ($variant['stock'] < $quantity) {
        echo json_encode([
            'success' => false,
            'message' => 'Insufficient stock. Only ' . $variant['stock'] . ' items available.'
        ]);
        exit;
    }
    
    // Check if item already exists in cart
    $check_cart = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND variant_id = ? AND size = ?";
    $stmt = $conn->prepare($check_cart);
    $stmt->bind_param("iiis", $user_id, $product_id, $variant_id, $size);
    $stmt->execute();
    $result = $stmt->get_result();
    $existing_item = $result->fetch_assoc();
    $stmt->close();
    
    if ($existing_item) {
        // Update quantity if item exists
        $new_quantity = $existing_item['quantity'] + $quantity;
        
        // Check if new quantity exceeds stock
        if ($new_quantity > $variant['stock']) {
            echo json_encode([
                'success' => false,
                'message' => 'Cannot add more items. Total would exceed available stock.'
            ]);
            exit;
        }
        
        $update_cart = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = $conn->prepare($update_cart);
        $stmt->bind_param("ii", $new_quantity, $existing_item['id']);
        $stmt->execute();
        $stmt->close();
        
        $message = 'Cart updated! Quantity increased to ' . $new_quantity;
    } else {
        // Insert new cart item
        $insert_cart = "INSERT INTO cart (user_id, product_id, variant_id, size, quantity, price, created_at) 
                       VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $conn->prepare($insert_cart);
        $price = floatval($data['product_price']);
        $stmt->bind_param("iiisid", $user_id, $product_id, $variant_id, $size, $quantity, $price);
        $stmt->execute();
        $stmt->close();
        
        $message = 'Product added to cart successfully!';
    }
    
    // Get total cart count
    $count_query = "SELECT SUM(quantity) as total FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($count_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $count_data = $result->fetch_assoc();
    $cart_count = $count_data['total'] ?? 0;
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'cart_count' => $cart_count
    ]);
    
} catch (Exception $e) {
    error_log('Cart error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again.'
    ]);
}
?>