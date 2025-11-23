<?php
// includes/product_quickview.php
// Ito ang file na tatawagin ng AJAX/JavaScript

// Assuming database.php is in ../config/
require_once '../config/database.php';

// I-check kung may valid na Product ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400); // Bad Request
    echo "Product ID is missing or invalid.";
    exit();
}

$product_id = intval($_GET['id']);

// Kumuha ng Product Details
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        -- Assuming products table has a 'category' column directly, as per database.sql snippet, 
        -- we will simplify the query.
        WHERE p.id = ?";

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404); // Not Found
    echo "Product not found.";
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();
?>

<div class="row">
    <div class="col-md-6">
        <img src="../uploads/products/<?php echo htmlspecialchars($product['image']); ?>" 
             class="img-fluid rounded shadow-sm quickview-image" 
             alt="<?php echo htmlspecialchars($product['name']); ?>">
        
        <?php if ($product['sale']): ?>
            <span class="badge sale-badge-quickview">SALE</span>
        <?php endif; ?>
    </div>
    
    <div class="col-md-6">
        <h2 class="quickview-title"><?php echo htmlspecialchars($product['name']); ?></h2>
        
        <p class="quickview-category text-muted text-uppercase small">
            Category: <span class="fw-bold"><?php echo htmlspecialchars($product['category']); ?></span>
        </p>

        <div class="mb-3 d-flex align-items-center">
            <?php echo generateStars($product['rating']); ?>
            <span class="text-muted small ms-2">(<?php echo htmlspecialchars($product['stock']); ?> in stock)</span>
        </div>
        
        <h3 class="quickview-price mb-3">
            <?php echo formatPrice($product['price']); ?>
            <?php if ($product['sale'] && $product['original_price'] > $product['price']): ?>
                <span class="original-price-quickview text-decoration-line-through text-muted ms-3">
                    <?php echo formatPrice($product['original_price']); ?>
                </span>
            <?php endif; ?>
        </h3>
        
        <p class="quickview-description text-muted small">
            <?php echo nl2br(htmlspecialchars($product['description'])); ?>
        </p>

        <hr>

        <form id="quickViewCartForm" action="../includes/add_to_cart.php" method="POST" class="mt-4">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            
            <div class="mb-3 d-flex align-items-center">
                <label for="quantity" class="form-label me-3 mb-0 fw-bold text-white">Qty:</label>
                <input type="number" 
                       id="quantity" 
                       name="quantity" 
                       class="form-control form-control-sm w-25 text-center" 
                       value="1" 
                       min="1" 
                       max="<?php echo htmlspecialchars($product['stock']); ?>"
                       required>
            </div>

            <button type="submit" class="btn btn-add-product w-100 mt-2" 
                    <?php echo ($product['stock'] <= 0) ? 'disabled' : ''; ?>>
                <?php if ($product['stock'] <= 0): ?>
                    <i class="fas fa-exclamation-circle me-2"></i> Sold Out
                <?php else: ?>
                    <i class="fas fa-shopping-cart me-2"></i> ADD TO CART
                <?php endif; ?>
            </button>
            <a href="../product_detail.php?id=<?php echo $product_id; ?>" class="btn btn-secondary w-100 mt-2">
                View Full Details
            </a>
        </form>
        
        
    </div>
</div>