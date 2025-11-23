<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Get search parameter
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query with search filter
$where_clause = '';
if (!empty($search)) {
    $where_clause = "WHERE (p.name LIKE ? OR p.brand LIKE ?)";
}

// Get all products with variant count
$products_query = "SELECT p.*, COUNT(pv.id) as variant_count, SUM(pv.stock) as total_stock
                   FROM products p
                   LEFT JOIN product_variants pv ON p.id = pv.product_id
                   {$where_clause}
                   GROUP BY p.id
                   ORDER BY p.created_at DESC";

if (!empty($search)) {
    $search_param = "%{$search}%";
    $stmt = $conn->prepare($products_query);
    $stmt->bind_param('ss', $search_param, $search_param);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $conn->query($products_query);
}

$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);

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
    <title>Product Management - LEE Sneakers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .variant-preview {
            display: inline-block;
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            margin: 2px;
            border: 2px solid #ddd;
        }
        .variant-input-group {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 2px solid #dee2e6;
        }
        .remove-variant-btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .category-badge {
            display: inline-block;
            margin: 2px;
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
        .search-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .search-results-info {
            padding: 10px 0;
            color: #666;
            font-size: 14px;
        }
        .duplicate-check {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            display: none;
        }
        .duplicate-warning {
            color: #856404;
            font-size: 13px;
        }
        .clear-filters-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .clear-filters-btn:hover {
            background: #5a6268;
        }
        .brand-management-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .brand-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border: 2px solid #dee2e6;
        }
        .brand-item:hover {
            border-color: #FEC700;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">LEE ADMIN</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
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
            <h1 class="admin-title">Product Management</h1>
            <p class="mb-0">Manage your sneaker inventory with multi-category, multi-brand, and multi-variant system</p>
        </div>
    </div>

    <div class="container py-5">
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $success; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Brand Management Section -->
        <div class="brand-management-section">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4><i class="fas fa-tags me-2"></i>Brand Management</h4>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBrandModal">
                    <i class="fas fa-plus me-2"></i>Add New Brand
                </button>
            </div>
            <div class="row">
                <?php foreach ($available_brands as $brand): ?>
                <div class="col-md-4">
                    <div class="brand-item">
                        <span><strong><?php echo htmlspecialchars($brand['name']); ?></strong></span>
                        <a href="delete_brand.php?id=<?php echo $brand['id']; ?>" 
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Are you sure you want to delete this brand? Products using this brand will need to be updated.')">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Search Box -->
        <div class="search-box">
            <form method="GET" action="products.php" id="searchForm">
                <label class="form-label mb-2 text-center"><i class="fas fa-search me-2"></i>Search Products</label>
                <div class="row g-2.5 justify-content-center">
                    <div class="col-md-8">
                        <input type="text" class="form-control" name="search" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Search by product name or brand...">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                    </div>
                </div>
            </form>
            
            <?php if (!empty($search)): ?>
                <div class="search-results-info mt-3 text-center">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Search Results:</strong> keyword: '<strong><?php echo htmlspecialchars($search); ?></strong>' - Found <?php echo $products->num_rows; ?> product(s)
                    <a href="products.php" class="btn btn-sm btn-secondary ms-2">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="admin-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>All Products (<?php echo $products->num_rows; ?>)</h3>
                <button class="btn btn-add-product" data-bs-toggle="modal" data-bs-target="#addProductModal">
                    <i class="fas fa-plus me-2"></i>Add New Product
                </button>
            </div>

            <div class="table-responsive">
                <table class="product-table table">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Brand</th>
                            <th>Variants</th>
                            <th>Price</th>
                            <th>Categories</th>
                            <th>Total Stock</th>
                            <th>Rating</th>
                            <th>Sale</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($products->num_rows > 0): ?>
                            <?php while ($product = $products->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($product['name']); ?></strong></td>
                                    <td>
                                        <?php if (!empty($product['brand'])): ?>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($product['brand']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">No brand</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $variants_query = "SELECT image FROM product_variants WHERE product_id = {$product['id']} ORDER BY variant_order LIMIT 3";
                                        $variants = $conn->query($variants_query);
                                        while ($variant = $variants->fetch_assoc()):
                                        ?>
                                            <img src="../uploads/products/<?php echo $variant['image']; ?>" 
                                                 class="variant-preview" alt="Variant">
                                        <?php endwhile; ?>
                                        <?php if ($product['variant_count'] > 3): ?>
                                            <span class="badge bg-secondary">+<?php echo $product['variant_count'] - 3; ?></span>
                                        <?php endif; ?>
                                        <div><small class="text-muted"><?php echo $product['variant_count']; ?> variant(s)</small></div>
                                    </td>
                                    <td><?php echo formatPrice($product['price']); ?></td>
                                    <td>
                                        <?php 
                                        if (!empty($product['category'])) {
                                            $categories = explode(',', $product['category']);
                                            foreach ($categories as $cat):
                                                $cat = trim($cat);
                                                if (!empty($cat)):
                                        ?>
                                            <span class="badge bg-secondary category-badge"><?php echo htmlspecialchars($cat); ?></span>
                                        <?php 
                                                endif;
                                            endforeach;
                                        } else {
                                            echo '<span class="text-muted">No category</span>';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $product['total_stock'] ?? 0; ?></td>
                                    <td><?php echo generateStars($product['rating']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $product['sale'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $product['sale'] ? 'Yes' : 'No'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-edit btn-sm" title="Edit Product">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="delete_product.php?id=<?php echo $product['id']; ?>" 
                                           class="btn btn-delete btn-sm"
                                           title="Delete Product"
                                           onclick="return confirm('Are you sure? This will delete all variants.')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    <?php if (!empty($search)): ?>
                                        <i class="fas fa-search me-2"></i>No products found matching your search criteria.
                                    <?php else: ?>
                                        No products found
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Brand Modal -->
    <div class="modal fade" id="addBrandModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Brand</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_brand.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Brand Name *</label>
                            <input type="text" class="form-control" name="brand_name" required placeholder="e.g., Nike, Adidas">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Add Brand
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Product (Multi-Category, Brand & Multi-Variant)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form action="add_product.php" method="POST" enctype="multipart/form-data" id="addProductForm">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product Name *</label>
                                <input type="text" class="form-control" name="name" id="productName" required>
                                <div class="duplicate-check" id="duplicateCheck">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <span class="duplicate-warning" id="duplicateWarning"></span>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Brand *</label>
                                <select class="form-select" name="brand" id="productBrand" required>
                                    <option value="">Select Brand</option>
                                    <?php foreach ($available_brands as $brand): ?>
                                        <option value="<?php echo htmlspecialchars($brand['name']); ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Categories * (Select multiple)</label>
                            
                            <div class="category-section">
                                <h6><i class="fas fa-tags me-2"></i>General Categories</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="New Arrivals" id="catNewArrivals">
                                    <label class="form-check-label" for="catNewArrivals">New Arrivals</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Best Seller" id="catBestSeller">
                                    <label class="form-check-label" for="catBestSeller">Best Seller</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Men" id="catMen">
                                    <label class="form-check-label" for="catMen">For Men (General)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Women" id="catWomen">
                                    <label class="form-check-label" for="catWomen">For Women (General)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Kids" id="catKids">
                                    <label class="form-check-label" for="catKids">For Kids</label>
                                </div>
                            </div>

                            <div class="category-section">
                                <h6><i class="fas fa-male me-2"></i>For Men - Specific</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Basketball Shoes" id="catBasketball">
                                    <label class="form-check-label" for="catBasketball">Basketball Shoes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Running Shoes Men" id="catRunningMen">
                                    <label class="form-check-label" for="catRunningMen">Running Shoes (Men)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Lifestyle Men" id="catLifestyleMen">
                                    <label class="form-check-label" for="catLifestyleMen">Lifestyle (Men)</label>
                                </div>
                            </div>

                            <div class="category-section">
                                <h6><i class="fas fa-female me-2"></i>For Women - Specific</h6>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Running Shoes Women" id="catRunningWomen">
                                    <label class="form-check-label" for="catRunningWomen">Running Shoes (Women)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="categories[]" value="Lifestyle Women" id="catLifestyleWomen">
                                    <label class="form-check-label" for="catLifestyleWomen">Lifestyle (Women)</label>
                                </div>
                            </div>

                            <small class="text-muted">Select at least one category</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Price (₱) *</label>
                                <input type="number" class="form-control" name="price" step="0.01" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Original Price (₱)</label>
                                <input type="number" class="form-control" name="original_price" step="0.01">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rating (1-5) *</label>
                                <input type="number" class="form-control" name="rating" min="1" max="5" step="0.5" value="4" required>
                            </div>
                            <div class="col-md-6 mb-3 form-check" style="padding-top: 40px;">
                                <input type="checkbox" class="form-check-input" name="sale" id="saleAdd">
                                <label class="form-check-label" for="saleAdd">Mark as On Sale</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="2"></textarea>
                        </div>

                        <hr class="my-4">
                        <h5 class="mb-3">Product Variants (Images with Sizes & Stock)</h5>
                        <p class="text-muted small">Upload multiple images. Each image represents a variant with specific sizes and stock.</p>

                        <div id="variantsContainer">
                            <div class="variant-input-group position-relative" data-variant-index="0">
                                <button type="button" class="btn btn-sm btn-danger remove-variant-btn" onclick="removeVariant(this)" style="display:none;">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Variant Image *</label>
                                        <input type="file" class="form-control variant-image" name="variant_images[]" accept="image/*" required>
                                        <small class="text-muted">JPG, PNG, WEBP (Max 5MB)</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Size *</label>
                                        <input type="text" class="form-control" name="variant_sizes[]" placeholder="e.g., 6, 7, 8, 9" required>
                                        <small class="text-muted">Single size or comma-separated</small>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Stock Quantity *</label>
                                        <input type="number" class="form-control" name="variant_stocks[]" min="0" value="0" required>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" class="btn btn-outline-primary" onclick="addVariantInput()">
                            <i class="fas fa-plus me-2"></i>Add Another Variant
                        </button>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-add-product">
                            <i class="fas fa-save me-2"></i>Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let variantIndex = 1;

        function addVariantInput() {
            const container = document.getElementById('variantsContainer');
            const newVariant = document.createElement('div');
            newVariant.className = 'variant-input-group position-relative';
            newVariant.setAttribute('data-variant-index', variantIndex);
            newVariant.innerHTML = `
                <button type="button" class="btn btn-sm btn-danger remove-variant-btn" onclick="removeVariant(this)">
                    <i class="fas fa-times"></i>
                </button>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Variant Image *</label>
                        <input type="file" class="form-control variant-image" name="variant_images[]" accept="image/*" required>
                        <small class="text-muted">JPG, PNG, WEBP (Max 5MB)</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Size *</label>
                        <input type="text" class="form-control" name="variant_sizes[]" placeholder="e.g., 6, 7, 8, 9" required>
                        <small class="text-muted">Single size or comma-separated</small>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Stock Quantity *</label>
                        <input type="number" class="form-control" name="variant_stocks[]" min="0" value="0" required>
                    </div>
                </div>
            `;
            container.appendChild(newVariant);
            variantIndex++;
            updateRemoveButtons();
        }

        function removeVariant(button) {
            button.closest('.variant-input-group').remove();
            updateRemoveButtons();
        }

        function updateRemoveButtons() {
            const variants = document.querySelectorAll('.variant-input-group');
            variants.forEach((variant, index) => {
                const removeBtn = variant.querySelector('.remove-variant-btn');
                if (variants.length > 1) {
                    removeBtn.style.display = 'block';
                } else {
                    removeBtn.style.display = 'none';
                }
            });
        }

        // Duplicate check functionality
        let checkTimeout;
        const productNameInput = document.getElementById('productName');
        const productBrandSelect = document.getElementById('productBrand');
        const duplicateCheck = document.getElementById('duplicateCheck');
        const duplicateWarning = document.getElementById('duplicateWarning');

        function checkDuplicates() {
            const name = productNameInput.value.trim();
            const brand = productBrandSelect.value;

            if (name.length < 3) {
                duplicateCheck.style.display = 'none';
                return;
            }

            clearTimeout(checkTimeout);
            checkTimeout = setTimeout(() => {
                const existingProducts = document.querySelectorAll('.product-table tbody tr td:first-child strong');
                let duplicateFound = false;
                let matchedProduct = '';

                existingProducts.forEach(productCell => {
                    const productName = productCell.textContent.toLowerCase();
                    if (productName.includes(name.toLowerCase())) {
                        duplicateFound = true;
                        matchedProduct = productCell.textContent;
                    }
                });

                if (duplicateFound) {
                    duplicateWarning.innerHTML = `Similar product found: "<strong>${matchedProduct}</strong>". Please verify before adding.`;
                    duplicateCheck.style.display = 'block';
                } else {
                    duplicateCheck.style.display = 'none';
                }
            }, 500);
        }

        productNameInput.addEventListener('input', checkDuplicates);
        productBrandSelect.addEventListener('change', checkDuplicates);

        document.addEventListener('DOMContentLoaded', updateRemoveButtons);
    </script>
</body>
</html>