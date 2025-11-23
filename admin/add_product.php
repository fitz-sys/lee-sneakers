<?php
require_once '../config/database.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $brand = sanitize($_POST['brand']);
    
    // Handle multiple categories - join with comma
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    if (empty($categories)) {
        $_SESSION['error'] = 'Please select at least one category.';
        redirect('products.php');
    }
    $category = implode(',', $categories);
    
    $price = floatval($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
    $rating = floatval($_POST['rating']);
    $description = sanitize($_POST['description']);
    $sale = isset($_POST['sale']) ? 1 : 0;

    // Validate inputs
    if (empty($name) || empty($brand) || $price <= 0) {
        $_SESSION['error'] = 'Please fill in all required fields.';
        redirect('products.php');
    }

    // Check if variant images are uploaded
    if (!isset($_FILES['variant_images']) || empty($_FILES['variant_images']['name'][0])) {
        $_SESSION['error'] = 'Please upload at least one variant image.';
        redirect('products.php');
    }

    $conn->begin_transaction();

    try {
        // Insert product (without main image for now)
        $sql = "INSERT INTO products (name, image, price, original_price, category, brand, rating, sale, stock, description) 
                VALUES (?, '', ?, ?, ?, ?, ?, ?, 0, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sddssdis", $name, $price, $original_price, $category, $brand, $rating, $sale, $description);

        if (!$stmt->execute()) {
            throw new Exception('Failed to add product to database.');
        }

        $product_id = $conn->insert_id;
        $stmt->close();

        // Handle variant uploads
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];
        $total_stock = 0;
        $first_image = '';
        
        foreach ($_FILES['variant_images']['name'] as $key => $filename) {
            if ($_FILES['variant_images']['error'][$key] !== 0) {
                continue;
            }

            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $filesize = $_FILES['variant_images']['size'][$key];

            // Validate file type
            if (!in_array($filetype, $allowed)) {
                throw new Exception('Invalid file type. Only JPG, PNG, WEBP, and JFIF are allowed.');
            }

            // Validate file size (5MB max)
            if ($filesize > 5 * 1024 * 1024) {
                throw new Exception('File size exceeds 5MB.');
            }

            // Generate unique filename
            $new_filename = uniqid() . '_' . time() . '_' . $key . '.' . $filetype;
            $upload_path = '../uploads/products/' . $new_filename;

            // Create directory if not exists
            if (!file_exists('../uploads/products/')) {
                mkdir('../uploads/products/', 0777, true);
            }

            // Move uploaded file
            if (!move_uploaded_file($_FILES['variant_images']['tmp_name'][$key], $upload_path)) {
                throw new Exception('Failed to upload variant image.');
            }

            // Store first image as main product image
            if ($key === 0) {
                $first_image = $new_filename;
            }

            // Insert variant
            $size = sanitize($_POST['variant_sizes'][$key]);
            $stock = intval($_POST['variant_stocks'][$key]);
            $variant_order = $key;

            $variant_sql = "INSERT INTO product_variants (product_id, image, size, stock, variant_order) 
                           VALUES (?, ?, ?, ?, ?)";
            $variant_stmt = $conn->prepare($variant_sql);
            $variant_stmt->bind_param("issii", $product_id, $new_filename, $size, $stock, $variant_order);
            
            if (!$variant_stmt->execute()) {
                throw new Exception('Failed to add variant.');
            }
            
            $variant_stmt->close();
            $total_stock += $stock;
        }

        // Update product with first image and total stock
        $update_sql = "UPDATE products SET image = ?, stock = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("sii", $first_image, $total_stock, $product_id);
        $update_stmt->execute();
        $update_stmt->close();

        $conn->commit();
        $_SESSION['success'] = 'Product "' . $name . '" (' . $brand . ') added successfully with ' . count($_FILES['variant_images']['name']) . ' variant(s)!';
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }

    redirect('products.php');
} else {
    redirect('products.php');
}
?>