<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($order_id <= 0) {
    redirect('orders.php');
}

// Get order details with user information
$order_query = "SELECT o.*, u.username, u.email, u.full_name 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ?";
                
$stmt = $conn->prepare($order_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();
$stmt->close();

if (!$order) {
    $_SESSION['error'] = 'Order not found.';
    redirect('orders.php');
}

// Decode shipping address JSON
$shipping_info = json_decode($order['shipping_address'], true);

// Get order items with product details
$items_query = "SELECT oi.*, p.name as product_name, pv.image 
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                LEFT JOIN product_variants pv ON oi.variant_id = pv.id
                WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order['id']; ?> - LEE Sneakers Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .order-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #333 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .info-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .info-card h5 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #FEC700;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: 600;
            color: #666;
        }
        .info-value {
            color: #333;
        }
        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        .status-badge {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            display: inline-block;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-confirmed {
            background: #cfe2ff;
            color: #084298;
        }
        .status-processing {
            background: #cfe2ff;
            color: #084298;
        }
        .status-shipped {
            background: #d1e7dd;
            color: #0a3622;
        }
        .status-delivered {
            background: #d1e7dd;
            color: #0f5132;
        }
        .status-completed {
            background: #d1e7dd;
            color: #0f5132;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #842029;
        }
        
        /* GCash Screenshot Styles */
        .gcash-screenshot-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .gcash-screenshot-img {
            max-width: 100%;
            max-height: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .gcash-screenshot-img:hover {
            transform: scale(1.02);
        }
        
        .no-screenshot {
            padding: 40px;
            text-align: center;
            color: #999;
        }
        
        .no-screenshot i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ddd;
        }
        
        /* Modal for full-size image */
        .modal-body img {
            width: 100%;
            height: auto;
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
                    <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link active" href="orders.php">Orders</a></li>
                </ul>
                <a href="../includes/logout.php" class="btn btn-logout ms-3">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="order-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2>Order #<?php echo $order['id']; ?></h2>
                    <p class="mb-0">Placed on <?php echo date('F d, Y \a\t g:i A', strtotime($order['created_at'])); ?></p>
                </div>
                <a href="orders.php" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Orders
                </a>
            </div>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row">
            <!-- Order Status & Actions -->
            <div class="col-md-4 mb-4">
                <div class="info-card">
                    <h5><i class="fas fa-info-circle me-2"></i>Order Status</h5>
                    <div class="text-center mb-3">
                        <span class="status-badge status-<?php echo $order['status']; ?>">
                            <?php echo strtoupper($order['status']); ?>
                        </span>
                    </div>
                    
                    <?php if ($order['status'] !== 'completed' && $order['status'] !== 'cancelled'): ?>
                    <form method="POST" action="update_order_status.php">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">Update Status:</label>
                            <select name="status" class="form-select" required>
                                <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="confirmed" <?php echo $order['status'] === 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-2"></i>Update Status
                        </button>
                    </form>
                    <?php endif; ?>
                </div>

                <div class="info-card">
                    <h5><i class="fas fa-user me-2"></i>Customer Details</h5>
                    <div class="info-row">
                        <span class="info-label">Name:</span>
                        <span class="info-value"><?php echo htmlspecialchars($shipping_info['full_name']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?php echo htmlspecialchars($shipping_info['email']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value"><?php echo htmlspecialchars($shipping_info['phone']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Order Items & Details -->
            <div class="col-md-8">
                <div class="info-card">
                    <h5><i class="fas fa-shopping-bag me-2"></i>Order Items</h5>
                    <div class="table-responsive">
                        <table class="table table-borderless">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Details</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($item = $items_result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <img src="../uploads/products/<?php echo $item['image']; ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                             class="item-image">
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($item['product_name']); ?></strong><br>
                                        <small class="text-muted">Size: <?php echo $item['size']; ?></small>
                                    </td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong></td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <div class="info-row">
                            <span class="info-label">Subtotal:</span>
                            <span class="info-value"><?php echo formatPrice($order['total_amount']); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Shipping:</span>
                            <span class="info-value">FREE</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label h5 mb-0">Total:</span>
                            <span class="info-value h5 mb-0 text-primary"><?php echo formatPrice($order['total_amount']); ?></span>
                        </div>
                    </div>
                </div>

                <div class="info-card">
                    <h5><i class="fas fa-map-marker-alt me-2"></i>Shipping Address</h5>
                    <p class="mb-2"><strong><?php echo htmlspecialchars($shipping_info['full_name']); ?></strong></p>
                    <p class="mb-1"><?php echo htmlspecialchars($shipping_info['street']); ?></p>
                    <p class="mb-1"><?php echo htmlspecialchars($shipping_info['barangay']); ?></p>
                    <p class="mb-1"><?php echo htmlspecialchars($shipping_info['city']) . ', ' . htmlspecialchars($shipping_info['province']); ?></p>
                    <p class="mb-1"><?php echo htmlspecialchars($shipping_info['postal_code']); ?></p>
                    <p class="mb-0"><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($shipping_info['phone']); ?></p>
                </div>

                <div class="info-card">
                    <h5><i class="fas fa-credit-card me-2"></i>Payment Information</h5>
                    <div class="info-row">
                        <span class="info-label">Payment Method:</span>
                        <span class="info-value"><?php echo strtoupper($order['payment_method']); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Payment Status:</span>
                        <span class="info-value">
                            <?php if ($order['status'] === 'completed'): ?>
                                <span class="badge bg-success">Paid</span>
                            <?php elseif ($order['status'] === 'cancelled'): ?>
                                <span class="badge bg-danger">Cancelled</span>
                            <?php else: ?>
                                <span class="badge bg-warning">Pending</span>
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <!-- GCash Payment Screenshot Section -->
                <?php if ($order['payment_method'] === 'GCash'): ?>
                <div class="info-card">
                    <h5><i class="fas fa-image me-2"></i>GCash Payment Proof</h5>
                    
                    <?php if (!empty($order['gcash_screenshot'])): ?>
                        <?php 
                        $screenshot_path = '../uploads/gcash_payments/' . $order['gcash_screenshot'];
                        if (file_exists($screenshot_path)): 
                        ?>
                            <div class="gcash-screenshot-container">
                                <img src="<?php echo $screenshot_path; ?>" 
                                     alt="GCash Payment Screenshot" 
                                     class="gcash-screenshot-img"
                                     data-bs-toggle="modal" 
                                     data-bs-target="#screenshotModal">
                                <p class="mt-3 mb-0 text-muted">
                                    <small>Click image to view full size</small>
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Screenshot file not found on server. File may have been deleted.
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="no-screenshot">
                            <i class="fas fa-file-image"></i>
                            <p>No payment screenshot uploaded</p>
                            <small class="text-muted">Customer did not upload proof of payment</small>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Screenshot Modal -->
    <div class="modal fade" id="screenshotModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">GCash Payment Screenshot - Order #<?php echo $order['id']; ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <?php if (!empty($order['gcash_screenshot'])): ?>
                        <img src="../uploads/gcash_payments/<?php echo $order['gcash_screenshot']; ?>" 
                             alt="GCash Payment Screenshot" 
                             style="max-width: 100%; height: auto;">
                    <?php endif; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>