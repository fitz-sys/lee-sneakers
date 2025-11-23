<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lee_sneakers');
define('GOOGLE_MAPS_API_KEY', 'YOUR_ACTUAL_API_KEY_HERE');


// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);


// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Set charset to utf8mb4
$conn->set_charset("utf8mb4");


// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Helper function to check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}


// Helper function to check if user is admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}


// Helper function to redirect
function redirect($url) {
    header("Location: $url");
    exit();
}


// Helper function to sanitize input
function sanitize($data) {
    global $conn;
    return mysqli_real_escape_string($conn, trim($data));
}


// Helper function to format price
function formatPrice($price) {
    return '₱' . number_format($price, 2);
}


// Helper function to generate star rating HTML
function generateStars($rating) {
    $stars = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $stars .= '<i class="fas fa-star"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $stars .= '<i class="fas fa-star-half-alt"></i>';
        } else {
            $stars .= '<i class="far fa-star"></i>';
        }
    }
    return $stars;
}
?>

