<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    redirect('products.php');
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $brand = sanitize($_POST['brand']);
    
    // Handle multiple categories
    $categories = isset($_POST['categories']) ? $_POST['categories'] : [];
    if (empty($categories)) {
        $_SESSION['error'] = 'Please select at least one category.';
        redirect('edit_product.php?id=' . $product_id);
    }
    $category = implode(',', $categories);
    
    $price = floatval($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
    $rating = floatval($_POST['rating']);
    $description = sanitize($_POST['description']);
    $sale = isset($_POST['sale']) ? 1 : 0;

    $conn->begin_transaction();

    try {
        // Update product basic info
        $sql = "UPDATE products SET name=?, price=?, original_price=?, category=?, brand=?, rating=?, sale=?, description=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sddssdssi", $name, $price, $original_price, $category, $brand, $rating, $sale, $description, $product_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update product.');
        }
        $stmt->close();

        // Handle variant updates and deletions
        $existing_variant_ids = $_POST['existing_variant_ids'] ?? [];
        $variant_sizes_existing = $_POST['variant_sizes_existing'] ?? [];
        $variant_stocks_existing = $_POST['variant_stocks_existing'] ?? [];
        
        // --- Image Upload Helper Function ---
        function handleImageUpload($file_array, $key, $old_filename = null) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp', 'jfif'];
            $filename = $file_array['name'][$key];
            $filetype = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            $filesize = $file_array['size'][$key];
            $tmp_name = $file_array['tmp_name'][$key];
            $error = $file_array['error'][$key];
            
            if ($error !== 0) {
                return null; // No file uploaded or upload error
            }

            if (!in_array($filetype, $allowed)) {
                throw new Exception('Invalid file type. Only JPG, PNG, WEBP, and JFIF are allowed.');
            }

            if ($filesize > 5 * 1024 * 1024) {
                throw new Exception('File size exceeds 5MB.');
            }

            $new_filename = uniqid() . '_' . time() . '_' . $key . '.' . $filetype;
            $upload_path = '../uploads/products/' . $new_filename;

            if (!file_exists('../uploads/products/')) {
                mkdir('../uploads/products/', 0777, true);
            }

            if (!move_uploaded_file($tmp_name, $upload_path)) {
                throw new Exception('Failed to upload variant image.');
            }
            
            // Delete old file if provided
            if ($old_filename) {
                $old_path = '../uploads/products/' . $old_filename;
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }
            
            return $new_filename;
        }
        // --- End Image Upload Helper Function ---
        
        // Get all current variants for deletion check and image info
        $current_variants_result = $conn->query("SELECT id, image FROM product_variants WHERE product_id = $product_id");
        $current_variant_ids = [];
        $current_variant_images = [];
        
        while ($row = $current_variants_result->fetch_assoc()) {
            $current_variant_ids[] = $row['id'];
            $current_variant_images[$row['id']] = $row['image'];
        }

        // Delete variants that were removed
        foreach ($current_variant_ids as $vid) {
            if (!in_array($vid, $existing_variant_ids)) {
                // Delete image file
                $image_path = '../uploads/products/' . $current_variant_images[$vid];
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
                // Delete from database
                $conn->query("DELETE FROM product_variants WHERE id = $vid");
            }
        }

        // Update existing variants
        $variant_images_existing = $_FILES['variant_images_existing'] ?? [];
        foreach ($existing_variant_ids as $key => $variant_id) {
            $size = sanitize($variant_sizes_existing[$key]);
            $stock = intval($variant_stocks_existing[$key]);
            $new_image_filename = null;
            $old_image_filename = $current_variant_images[$variant_id] ?? null;

            // Handle image update for existing variant
            if (isset($variant_images_existing['name'][$key]) && $variant_images_existing['error'][$key] !== 4) {
                $new_image_filename = handleImageUpload($variant_images_existing, $key, $old_image_filename);
            }
            
            if ($new_image_filename) {
                $update_variant = "UPDATE product_variants SET size=?, stock=?, image=? WHERE id=?";
                $stmt = $conn->prepare($update_variant);
                $stmt->bind_param("sisi", $size, $stock, $new_image_filename, $variant_id);
            } else {
                $update_variant = "UPDATE product_variants SET size=?, stock=? WHERE id=?";
                $stmt = $conn->prepare($update_variant);
                $stmt->bind_param("sii", $size, $stock, $variant_id);
            }

            $stmt->execute();
            $stmt->close();
        }

        // Handle new variant uploads
        if (isset($_FILES['variant_images_new']) && !empty($_FILES['variant_images_new']['name'][0])) {
            $max_order_result = $conn->query("SELECT MAX(variant_order) as max_order FROM product_variants WHERE product_id = $product_id");
            $max_order = $max_order_result->fetch_assoc()['max_order'] ?? -1;
            
            foreach ($_FILES['variant_images_new']['name'] as $key => $filename) {
                if ($_FILES['variant_images_new']['error'][$key] !== 0) {
                    continue;
                }
                
                // Use the helper function for new uploads (no old file to delete)
                $new_filename = handleImageUpload($_FILES['variant_images_new'], $key);

                $size = sanitize($_POST['variant_sizes_new'][$key]);
                $stock = intval($_POST['variant_stocks_new'][$key]);
                $variant_order = $max_order + $key + 1;

                $insert_variant = "INSERT INTO product_variants (product_id, image, size, stock, variant_order) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_variant);
                $stmt->bind_param("issii", $product_id, $new_filename, $size, $stock, $variant_order);
                
                if (!$stmt->execute()) {
                    throw new Exception('Failed to insert new variant.');
                }
                $stmt->close();
            }
        }

        // Update total stock and main image
        $total_stock_result = $conn->query("SELECT SUM(stock) as total, MIN(image) as first_image FROM product_variants WHERE product_id = $product_id");
        $stock_data = $total_stock_result->fetch_assoc();
        $total_stock = $stock_data['total'] ?? 0;
        // Fetch the image from the variant with the lowest variant_order, or simply the first image if order is not guaranteed.
        // A safer approach for the main image is to select the image from the variant with the lowest variant_order.
        $main_image_result = $conn->query("SELECT image FROM product_variants WHERE product_id = $product_id ORDER BY variant_order ASC LIMIT 1");
        $first_image = $main_image_result->fetch_assoc()['image'] ?? '';


        $update_product = "UPDATE products SET stock=?, image=? WHERE id=?";
        $stmt = $conn->prepare($update_product);
        $stmt->bind_param("isi", $total_stock, $first_image, $product_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        $_SESSION['success'] = 'Product "' . $name . '" updated successfully!';
        redirect('products.php');
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
        redirect('edit_product.php?id=' . $product_id);
    }
}

// Get product details
$product_query = "SELECT * FROM products WHERE id = ?";
$stmt = $conn->prepare($product_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    redirect('products.php');
}

// Get all variants
$variants_query = "SELECT * FROM product_variants WHERE product_id = ? ORDER BY variant_order";
$stmt = $conn->prepare($variants_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$variants_result = $stmt->get_result();
$variants = [];
while ($row = $variants_result->fetch_assoc()) {
    $variants[] = $row;
}
$stmt->close();

// Parse existing categories
$existing_categories = explode(',', $product['category']);
$existing_categories = array_map('trim', $existing_categories);

// Get available brands from database
$brands_query = "SELECT * FROM brands ORDER BY name ASC";
$brands_result = $conn->query($brands_query);
$available_brands = [];
while ($brand = $brands_result->fetch_assoc()) {
    $available_brands[] = $brand;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - LEE Sneakers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .variant-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 2px solid #dee2e6;
            position: relative;
        }
        .variant-image-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #ddd;
        }
        .remove-variant-btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .new-variant-group {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 2px dashed #0d6efd;
            position: relative;
        }
        .category-section {
            border: 1px solid #dee2e6;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .category-section h6 {
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
        }
        /* Style for the image container */
        .variant-image-col {
            position: relative;
        }
        .image-update-container {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">LEE ADMIN</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                    </li>
                </ul>
                <a href="../includes/logout.php" class="btn btn-logout ms-3">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="admin-header">
        <div class="container">
            <h1 class="admin-title">Edit Product</h1>
            <p class="mb-0">Update product information, brand, and manage variants</p>
        </div>
    </div>

    <div class="container py-5">
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="admin-card">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <h4 class="mb-4">Basic Information</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Brand *</label>
                                <select class="form-select" name="brand" required>
                                    <option value="">Select Brand</option>
                                    <?php foreach ($available_brands as $brand): ?>
                                        <option value="<?php echo htmlspecialchars($brand['name']); ?>" <?php echo ($product['brand'] === $brand['name']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($brand['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Categories * (Select multiple)</label>
                            
                            <div class="category-section">
                                <h6><i class="fas fa-tags me-2"></i>General Categories</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="New Arrivals" id="catNewArrivals" <?php echo in_array('New Arrivals', $existing_categories) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="catNewArrivals">New Arrivals</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Best Seller" id="catBestSeller" <?php echo in_array('Best Seller', $existing_categories) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="catBestSeller">Best Seller</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Men" id="catMen" <?php echo in_array('Men', $existing_categories) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="catMen">For Men (General)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Women" id="catWomen" <?php echo in_array('Women', $existing_categories) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="catWomen">For Women (General)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Kids" id="catKids" <?php echo in_array('Kids', $existing_categories) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="catKids">For Kids</label>
                                </div>
                            </div>

                            <div class="category-section">
                                <h6><i class="fas fa-male me-2"></i>For Men - Specific</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Basketball Shoes" id="catBasketball" <?php echo in_array('Basketball Shoes', $existing_categories) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="catBasketball">Basketball Shoes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Running Shoes Men" id="catRunningMen" <?php echo in_array('Running Shoes Men', $existing_categories) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="catRunningMen">Running Shoes (Men)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Lifestyle Men" id="catLifestyleMen" <?php echo in_array('Lifestyle Men', $existing_categories) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="catLifestyleMen">Lifestyle (Men)</label>
                                </div>
                            </div>

                            <div class="category-section">
                                <h6><i class="fas fa-female me-2"></i>For Women - Specific</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Running Shoes Women" id="catRunningWomen" <?php echo in_array('Running Shoes Women', $existing_categories) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="catRunningWomen">Running Shoes (Women)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Lifestyle Women" id="catLifestyleWomen" <?php echo in_array('Lifestyle Women', $existing_categories) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="catLifestyleWomen">Lifestyle (Women)</label>
                                </div>
                            </div>

                            <small class="text-muted">Select at least one category</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (₱) *</label>
                                <input type="number" class="form-control" name="price" step="0.01" value="<?php echo $product['price']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Original Price (₱)</label>
                                <input type="number" class="form-control" name="original_price" step="0.01" value="<?php echo $product['original_price']; ?>">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rating *</label>
                                <input type="number" class="form-control" name="rating" min="1" max="5" step="0.5" value="<?php echo $product['rating']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3 form-check" style="padding-top: 40px;">
                                <input type="checkbox" class="form-check-input" name="sale" id="sale" <?php echo $product['sale'] ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="sale">Mark as On Sale</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($product['description']); ?></textarea>
                        </div>

                        <hr class="my-4">
                        
                        <h4 class="mb-3">Product Variants</h4>
                        <p class="text-muted">Manage existing variants or add new ones. Leave the **Variant Image** field blank to keep the current image.</p>

                        <div id="existingVariantsContainer">
                            <?php foreach ($variants as $index => $variant): ?>
                            <div class="variant-card" data-variant-id="<?php echo $variant['id']; ?>">
                                <input type="hidden" name="existing_variant_ids[]" value="<?php echo $variant['id']; ?>">
                                <button type="button" class="btn btn-sm btn-danger remove-variant-btn" onclick="removeExistingVariant(this, <?php echo $variant['id']; ?>)">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                                
                                <div class="row align-items-center">
                                    <div class="col-md-4 variant-image-col">
                                        <img src="../uploads/products/<?php echo $variant['image']; ?>" 
                                             class="variant-image-preview" alt="Variant <?php echo $index + 1; ?>">
                                        <div class="mt-2">
                                            <small class="text-muted">Variant #<?php echo $index + 1; ?></small>
                                        </div>
                                        <div class="image-update-container">
                                            <label class="form-label mt-2">Update Image (Optional)</label>
                                            <input type="file" class="form-control" 
                                                   name="variant_images_existing[]" 
                                                   accept="image/*">
                                            <small class="text-muted">Max 5MB. Leave blank to keep current.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Size(s) *</label>
                                        <input type="text" class="form-control" 
                                               name="variant_sizes_existing[]" 
                                               value="<?php echo htmlspecialchars($variant['size']); ?>" 
                                               placeholder="e.g., 6, 7, 8" required>
                                        <small class="text-muted">Single size or comma-separated</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Stock Quantity *</label>
                                        <input type="number" class="form-control" 
                                               name="variant_stocks_existing[]" 
                                               value="<?php echo $variant['stock']; ?>" 
                                               min="0" required>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <div id="newVariantsContainer"></div>

                        <button type="button" class="btn btn-outline-primary mb-4" onclick="addNewVariant()">
                            <i class="fas fa-plus me-2"></i>Add New Variant
                        </button>

                        <hr class="my-4">

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-add-product">
                                <i class="fas fa-save me-2"></i>Update Product
                            </button>
                            <a href="products.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let newVariantIndex = 0;

        function addNewVariant() {
            const container = document.getElementById('newVariantsContainer');
            const newVariant = document.createElement('div');
            newVariant.className = 'new-variant-group';
            newVariant.setAttribute('data-new-index', newVariantIndex);
            newVariant.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger remove-variant-btn" onclick="removeNewVariant(this)">
                    <i class="fas fa-times"></i>
                </button>
                <h6 class="text-primary mb-3"><i class="fas fa-plus-circle me-2"></i>New Variant</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Variant Image *</label>
                        <input type="file" class="form-control" name="variant_images_new[]" accept="image/*" required>
                        <small class="text-muted">JPG, PNG, WEBP (Max 5MB)</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Size(s) *</label>
                        <input type="text" class="form-control" name="variant_sizes_new[]" placeholder="e.g., 6, 7, 8" required>
                        <small class="text-muted">Single size or comma-separated</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" class="form-control" name="variant_stocks_new[]" min="0" value="0" required>
                    </div>
                </div>
            `;
            container.appendChild(newVariant);
            newVariantIndex++;
        }

        function removeNewVariant(button) {
            button.closest('.new-variant-group').remove();
        }

        function removeExistingVariant(button, variantId) {
            if (confirm('Are you sure you want to delete this variant? This action cannot be undone.')) {
                button.closest('.variant-card').remove();
                
                const remainingVariants = document.querySelectorAll('.variant-card').length;
                const newVariants = document.querySelectorAll('.new-variant-group').length;
                
                if (remainingVariants === 0 && newVariants === 0) {
                    alert('Warning: You must have at least one variant. Please add a new variant before saving.');
                }
            }
        }
    </script>
</body>
</html>