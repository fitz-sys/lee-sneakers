<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    redirect('users.php');
}

// Get user details
$user_query = "SELECT * FROM users WHERE id = ? AND role = 'user'";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    $_SESSION['error'] = 'User not found.';
    redirect('users.php');
}

// Get user's orders
$orders_query = "SELECT o.*, COUNT(oi.id) as item_count
                 FROM orders o
                 LEFT JOIN order_items oi ON o.id = oi.order_id
                 WHERE o.user_id = ?
                 GROUP BY o.id
                 ORDER BY o.created_at DESC";
$stmt = $conn->prepare($orders_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

// Get user's addresses
$addresses_query = "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
$stmt = $conn->prepare($addresses_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses = $stmt->get_result();
$stmt->close();

// Get statistics
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id")->fetch_assoc()['count'];
$total_spent = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE user_id = $user_id AND status = 'completed'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Details - LEE Sneakers Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .user-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
        }
        .user-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #667eea;
            font-size: 3rem;
            font-weight: bold;
            margin: 0 auto 20px;
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
        .stat-box {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .stat-box h3 {
            color: #000435;
            margin-bottom: 5px;
        }
        .stat-box p {
            color: #666;
            margin: 0;
            font-size: 0.9rem;
        }
        .address-card {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
        }
        .address-card.default {
            border-color: #28a745;
            background: #f8fff9;
        }
        .order-mini-card {
            border-left: 4px solid #FEC700;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .status-badge {
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending { background: #ffc107; color: #000; }
        .status-confirmed { background: #17a2b8; color: white; }
        .status-processing { background: #007bff; color: white; }
        .status-completed { background: #28a745; color: white; }
        .status-cancelled { background: #dc3545; color: white; }
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
                    <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                    <li class="nav-item"><a class="nav-link active" href="users.php">Users</a></li>
                </ul>
                <a href="../includes/logout.php" class="btn btn-logout ms-3">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="user-header">
        <div class="container text-center">
            <div class="user-avatar-large">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
            <p class="mb-0">@<?php echo htmlspecialchars($user['username']); ?> • Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="mb-3">
            <a href="users.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Users
            </a>
        </div>

        <div class="row">
            <!-- User Info & Stats -->
            <div class="col-md-4">
                <div class="info-card">
                    <h5><i class="fas fa-user me-2"></i>User Information</h5>
                    <p><strong>Email:</strong><br><?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Username:</strong><br><?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Joined:</strong><br><?php echo date('F d, Y', strtotime($user['created_at'])); ?></p>
                    <p class="mb-0"><strong>Account Type:</strong><br><span class="badge bg-primary">Customer</span></p>
                </div>

                <div class="info-card">
                    <h5><i class="fas fa-chart-line me-2"></i>Statistics</h5>
                    <div class="stat-box mb-3">
                        <h3><?php echo $total_orders; ?></h3>
                        <p>Total Orders</p>
                    </div>
                    <div class="stat-box">
                        <h3><?php echo formatPrice($total_spent); ?></h3>
                        <p>Total Spent</p>
                    </div>
                </div>
            </div>

            <!-- Orders & Addresses -->
            <div class="col-md-8">
                <!-- Orders -->
                <div class="info-card">
                    <h5><i class="fas fa-shopping-bag me-2"></i>Order History (<?php echo $total_orders; ?>)</h5>
                    <?php if ($orders->num_rows > 0): ?>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <div class="order-mini-card">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>Order #<?php echo $order['id']; ?></strong>
                                        <span class="status-badge status-<?php echo $order['status']; ?> ms-2">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($order['created_at'])); ?> • 
                                            <?php echo $order['item_count']; ?> item(s) • 
                                            <?php echo formatPrice($order['total_amount']); ?>
                                        </small>
                                    </div>
                                    <a href="view_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No orders yet</p>
                    <?php endif; ?>
                </div>

                <!-- Addresses -->
                <div class="info-card">
                    <h5><i class="fas fa-map-marker-alt me-2"></i>Saved Addresses (<?php echo $addresses->num_rows; ?>)</h5>
                    <?php if ($addresses->num_rows > 0): ?>
                        <?php while ($address = $addresses->fetch_assoc()): ?>
                            <?php $addr_data = json_decode($address['address_data'], true); ?>
                            <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>">
                                <?php if ($address['is_default']): ?>
                                    <span class="badge bg-success mb-2">Default Address</span>
                                <?php endif; ?>
                                <strong><?php echo htmlspecialchars($addr_data['first_name'] . ' ' . $addr_data['last_name']); ?></strong><br>
                                <small class="text-muted">
                                    <?php echo htmlspecialchars($addr_data['address']); ?><br>
                                    <?php if (!empty($addr_data['apartment'])): ?>
                                        <?php echo htmlspecialchars($addr_data['apartment']); ?><br>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($addr_data['city'] . ', ' . $addr_data['region'] . ' ' . $addr_data['postal_code']); ?><br>
                                    <i class="fas fa-phone me-1"></i><?php echo htmlspecialchars($addr_data['phone']); ?>
                                </small>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No saved addresses</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>