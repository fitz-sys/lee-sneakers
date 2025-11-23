<?php
require_once '../config/database.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id > 0) {
    $conn->begin_transaction();
    
    try {
        // Get all variant images before deleting
        $variants_query = "SELECT image FROM product_variants WHERE product_id = ?";
        $stmt = $conn->prepare($variants_query);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $variant_images = [];
        while ($row = $result->fetch_assoc()) {
            $variant_images[] = $row['image'];
        }
        $stmt->close();
        
        if (count($variant_images) > 0) {
            // Delete all variant images from filesystem
            foreach ($variant_images as $image) {
                $image_path = '../uploads/products/' . $image;
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
            
            // Delete all variants from database (will cascade if FK is set properly)
            $delete_variants = "DELETE FROM product_variants WHERE product_id = ?";
            $stmt = $conn->prepare($delete_variants);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $stmt->close();
        }
        
        // Delete product from database
        $delete_product = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($delete_product);
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $conn->commit();
            $_SESSION['success'] = 'Product and all its variants deleted successfully!';
        } else {
            throw new Exception('Failed to delete product.');
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = 'Failed to delete product: ' . $e->getMessage();
    }
} else {
    $_SESSION['error'] = 'Invalid product ID.';
}

redirect('products.php');
?>