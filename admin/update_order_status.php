<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('orders.php');
}

$order_id = intval($_POST['order_id']);
$new_status = sanitize($_POST['status']);

// Validate status
$valid_statuses = ['pending', 'confirmed', 'processing', 'completed', 'cancelled'];
if (!in_array($new_status, $valid_statuses)) {
    $_SESSION['error'] = 'Invalid status';
    redirect('view_order.php?id=' . $order_id);
}

// Update order status
$update_query = "UPDATE orders SET status = ?, updated_at = NOW() WHERE id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("si", $new_status, $order_id);

if ($stmt->execute()) {
    $_SESSION['success'] = 'Order status updated to ' . ucfirst($new_status) . ' successfully!';
} else {
    $_SESSION['error'] = 'Failed to update order status.';
}

$stmt->close();
redirect('view_order.php?id=' . $order_id);
?>