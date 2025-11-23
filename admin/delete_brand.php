<?php
require_once '../config/database.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$brand_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($brand_id > 0) {
    // Get brand name first
    $brand_query = "SELECT name FROM brands WHERE id = ?";
    $stmt = $conn->prepare($brand_query);
    $stmt->bind_param("i", $brand_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $brand = $result->fetch_assoc();
    $stmt->close();
    
    if ($brand) {
        $brand_name = $brand['name'];
        
        // Check if any products are using this brand
        $products_query = "SELECT COUNT(*) as count FROM products WHERE brand = ?";
        $stmt = $conn->prepare($products_query);
        $stmt->bind_param("s", $brand_name);
        $stmt->execute();
        $result = $stmt->get_result();
        $count_data = $result->fetch_assoc();
        $stmt->close();
        
        if ($count_data['count'] > 0) {
            $_SESSION['error'] = 'Cannot delete brand "' . $brand_name . '" because ' . $count_data['count'] . ' product(s) are using it. Please update or delete those products first.';
        } else {
            // Delete brand
            $delete_query = "DELETE FROM brands WHERE id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("i", $brand_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Brand "' . $brand_name . '" deleted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to delete brand.';
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = 'Brand not found.';
    }
} else {
    $_SESSION['error'] = 'Invalid brand ID.';
}

redirect('products.php');
?>