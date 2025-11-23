<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = file_get_contents('php://input');
$data = json_decode($input, true);

$cart_id = intval($data['cart_id']);
$quantity = intval($data['quantity']);
$user_id = $_SESSION['user_id'];

if ($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid quantity']);
    exit;
}

// Verify cart item belongs to user and check stock
$check_query = "SELECT c.id, pv.stock 
                FROM cart c
                JOIN product_variants pv ON c.variant_id = pv.id
                WHERE c.id = ? AND c.user_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $cart_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$cart_item = $result->fetch_assoc();
$stmt->close();

if (!$cart_item) {
    echo json_encode(['success' => false, 'message' => 'Cart item not found']);
    exit;
}

if ($quantity > $cart_item['stock']) {
    echo json_encode(['success' => false, 'message' => 'Insufficient stock']);
    exit;
}

// Update quantity
$update_query = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("iii", $quantity, $cart_id, $user_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Quantity updated']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update']);
}

$stmt->close();
?>