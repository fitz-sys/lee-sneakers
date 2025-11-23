<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Get filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query based on filter
$where_clause = $status_filter !== 'all' ? "WHERE o.status = ?" : "";

$orders_query = "SELECT o.*, u.username, u.full_name, u.email,
                 COUNT(oi.id) as item_count
                 FROM orders o
                 JOIN users u ON o.user_id = u.id
                 LEFT JOIN order_items oi ON o.id = oi.order_id
                 $where_clause
                 GROUP BY o.id
                 ORDER BY o.created_at DESC";

$stmt = $conn->prepare($orders_query);
if ($status_filter !== 'all') {
    $stmt->bind_param("s", $status_filter);
}
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

// Get order counts by status
$pending_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")->fetch_assoc()['count'];
$confirmed_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'confirmed'")->fetch_assoc()['count'];
$processing_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'processing'")->fetch_assoc()['count'];
$completed_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'completed'")->fetch_assoc()['count'];
$cancelled_count = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status = 'cancelled'")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - LEE Sneakers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .filter-tabs {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .filter-tab {
            display: inline-block;
            padding: 8px 20px;
            margin: 5px;
            border-radius: 20px;
            text-decoration: none;
            color: #666;
            background: #f8f9fa;
            transition: all 0.3s;
        }
        .filter-tab:hover {
            background: #e9ecef;
            color: #333;
        }
        .filter-tab.active {
            background: #333;
            color: white;
        }
        .order-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .order-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
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
        .action-buttons {
            display: flex;
            gap: 5px;
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
                    <li class="nav-item"><a class="nav-link" href="products.php">Products</a></li>
                    <li class="nav-item"><a class="nav-link active" href="orders.php">Orders</a></li>
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
            <h1 class="admin-title">Order Management</h1>
            <p class="mb-0">Monitor and manage customer orders</p>
        </div>
    </div>

    <div class="container py-5">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div class="filter-tabs">
            <a href="?status=all" class="filter-tab <?php echo $status_filter === 'all' ? 'active' : ''; ?>">
                All Orders
            </a>
            <a href="?status=pending" class="filter-tab <?php echo $status_filter === 'pending' ? 'active' : ''; ?>">
                Pending <span class="badge bg-warning text-dark"><?php echo $pending_count; ?></span>
            </a>
            <a href="?status=confirmed" class="filter-tab <?php echo $status_filter === 'confirmed' ? 'active' : ''; ?>">
                Confirmed <span class="badge bg-info"><?php echo $confirmed_count; ?></span>
            </a>
            <a href="?status=processing" class="filter-tab <?php echo $status_filter === 'processing' ? 'active' : ''; ?>">
                Processing <span class="badge bg-primary"><?php echo $processing_count; ?></span>
            </a>
            <a href="?status=completed" class="filter-tab <?php echo $status_filter === 'completed' ? 'active' : ''; ?>">
                Completed <span class="badge bg-success"><?php echo $completed_count; ?></span>
            </a>
            <a href="?status=cancelled" class="filter-tab <?php echo $status_filter === 'cancelled' ? 'active' : ''; ?>">
                Cancelled <span class="badge bg-danger"><?php echo $cancelled_count; ?></span>
            </a>
        </div>

        <!-- Orders List -->
        <?php if ($orders->num_rows > 0): ?>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <?php $shipping = json_decode($order['shipping_address'], true); ?>
                <div class="order-card">
                    <div class="row align-items-center">
                        <div class="col-md-2">
                            <h5 class="mb-1">Order #<?php echo $order['id']; ?></h5>
                            <small class="text-muted">
                                <?php echo date('M d, Y', strtotime($order['created_at'])); ?>
                            </small>
                        </div>
                        <div class="col-md-2">
                            <strong><?php echo htmlspecialchars($order['full_name']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Total Amount</small><br>
                            <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Items</small><br>
                            <strong><?php echo $order['item_count']; ?> item(s)</strong>
                        </div>
                        <div class="col-md-2 text-center">
                            <span class="status-badge status-<?php echo $order['status']; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </div>
                        <div class="col-md-2 text-end">
                            <div class="action-buttons">
                                <a href="view_order.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-sm btn-primary" 
                                   title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="delete_order.php?id=<?php echo $order['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   title="Delete Order"
                                   onclick="return confirm('Are you sure you want to delete Order #<?php echo $order['id']; ?>? This action cannot be undone. Stock will be restored if order was completed/processing.')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No orders found</h4>
                <p class="text-muted">Orders will appear here when customers make purchases.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>