<?php
require_once '../config/database.php';

// Store the user role before destroying session
$user_role = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

// Destroy all session data
session_destroy();

// Redirect based on role
if ($user_role === 'admin') {
    // Admin logout - redirect to main index
    redirect('../index.php');
} else {
    // User logout - redirect to user home page
    redirect('../user/index.php');
}
?>