<?php
require_once '../config/database.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brand_name = sanitize($_POST['brand_name']);

    // Validate input
    if (empty($brand_name)) {
        $_SESSION['error'] = 'Brand name is required.';
        redirect('products.php');
    }

    // Check if brand already exists
    $check_query = "SELECT id FROM brands WHERE name = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("s", $brand_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = 'Brand "' . $brand_name . '" already exists.';
        $stmt->close();
        redirect('products.php');
    }
    $stmt->close();

    // Insert new brand
    $insert_query = "INSERT INTO brands (name) VALUES (?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("s", $brand_name);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Brand "' . $brand_name . '" added successfully!';
    } else {
        $_SESSION['error'] = 'Failed to add brand.';
    }
    
    $stmt->close();
} else {
    $_SESSION['error'] = 'Invalid request method.';
}

redirect('products.php');
?>