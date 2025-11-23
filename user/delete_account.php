<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('profile.php');
}

$user_id = $_SESSION['user_id'];
$password = $_POST['password'] ?? '';

// Verify password before deleting
$user_query = "SELECT password FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['error'] = 'Incorrect password. Account deletion cancelled.';
    redirect('profile.php');
}

$conn->begin_transaction();

try {
    // Check for pending/processing orders
    $pending_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id AND status IN ('pending', 'confirmed', 'processing')")->fetch_assoc()['count'];
    
    if ($pending_orders > 0) {
        throw new Exception('You have pending orders. Please wait for them to be completed or cancelled before deleting your account.');
    }
    
    // Delete user addresses
    $delete_addresses = "DELETE FROM user_addresses WHERE user_id = ?";
    $stmt = $conn->prepare($delete_addresses);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Delete cart items
    $delete_cart = "DELETE FROM cart WHERE user_id = ?";
    $stmt = $conn->prepare($delete_cart);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
    
    // Note: Orders are kept for records but we could optionally delete them too
    // For now, we'll keep order history for business records
    
    // Delete the user account
    $delete_user = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($delete_user);
    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete account.');
    }
    
    $stmt->close();
    $conn->commit();
    
    // Logout user
    session_destroy();
    
    // Redirect to index with success message
    session_start();
    $_SESSION['success'] = 'Your account has been deleted successfully. Thank you for using LEE Sneakers.';
    redirect('../index.php');
    
} catch (Exception $e) {
    $conn->rollback();
    $_SESSION['error'] = $e->getMessage();
    redirect('profile.php');
}
?>