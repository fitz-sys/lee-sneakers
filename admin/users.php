<?php
require_once '../config/database.php';

if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Get all users (excluding admins)
$users_query = "SELECT u.*, 
                COUNT(DISTINCT o.id) as total_orders,
                COALESCE(SUM(o.total_amount), 0) as total_spent
                FROM users u
                LEFT JOIN orders o ON u.id = o.user_id
                WHERE u.role = 'user'
                GROUP BY u.id
                ORDER BY u.created_at DESC";
$users = $conn->query($users_query);

// Get statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$active_users = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM orders WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - LEE Sneakers Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .user-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .user-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
        }
        .user-stats {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }
        .user-stat {
            text-align: center;
        }
        .user-stat-value {
            font-size: 1.2rem;
            font-weight: bold;
            color: #000435;
        }
        .user-stat-label {
            font-size: 0.85rem;
            color: #666;
        }
        .stats-summary {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
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
                    <li class="nav-item"><a class="nav-link" href="orders.php">Orders</a></li>
                    <li class="nav-item"><a class="nav-link active" href="users.php">Users</a></li>
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
            <h1 class="admin-title">User Management</h1>
            <p class="mb-0">View and manage registered users</p>
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

        <!-- Stats Summary -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="stats-summary">
                    <h4 class="mb-3"><i class="fas fa-users me-2"></i>User Statistics</h4>
                    <div class="row">
                        <div class="col-6">
                            <div class="user-stat">
                                <div class="user-stat-value"><?php echo $total_users; ?></div>
                                <div class="user-stat-label">Total Users</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="user-stat">
                                <div class="user-stat-value"><?php echo $active_users; ?></div>
                                <div class="user-stat-label">Active (30 days)</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users List -->
        <?php if ($users->num_rows > 0): ?>
            <?php while ($user = $users->fetch_assoc()): ?>
                <div class="user-card">
                    <div class="row align-items-center">
                        <div class="col-md-1">
                            <div class="user-avatar">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h5 class="mb-1"><?php echo htmlspecialchars($user['full_name']); ?></h5>
                            <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Email</small><br>
                            <strong><?php echo htmlspecialchars($user['email']); ?></strong>
                        </div>
                        <div class="col-md-2">
                            <small class="text-muted">Joined</small><br>
                            <strong><?php echo date('M d, Y', strtotime($user['created_at'])); ?></strong>
                        </div>
                        <div class="col-md-2">
                            <div class="user-stats">
                                <div class="user-stat">
                                    <div class="user-stat-value"><?php echo $user['total_orders']; ?></div>
                                    <div class="user-stat-label">Orders</div>
                                </div>
                                <div class="user-stat">
                                    <div class="user-stat-value"><?php echo formatPrice($user['total_spent']); ?></div>
                                    <div class="user-stat-label">Total Spent</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-1 text-end">
                            <a href="view_user.php?id=<?php echo $user['id']; ?>" 
                               class="btn btn-sm btn-primary" 
                               title="View Details">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No users found</h4>
                <p class="text-muted">Users will appear here when they register.</p>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>