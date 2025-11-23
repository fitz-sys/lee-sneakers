<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn()) {
    echo json_encode([
        'success' => true,
        'cart_count' => 0
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

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
    'cart_count' => intval($cart_count)
]);
?>