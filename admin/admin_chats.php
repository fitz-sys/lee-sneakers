<?php
require_once '../config/database.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../index.php');
}

// Get chat messages (stored in session for now, can be expanded to database)
// For now, we'll create a structure to display chat interactions
$chats = [];

// This is a placeholder structure. In production, you'd query from a database table
// For example: SELECT * FROM chat_messages ORDER BY created_at DESC

// Sample data structure (replace with actual database query when implemented)
$sample_chats = [];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat Messages - LEE Sneakers Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .admin-header {
            background: linear-gradient(135deg, #000435 0%, #001a5e 100%);
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .admin-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0;
        }

        .navbar {
            background: #000435;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
            color: #FEC700 !important;
            letter-spacing: 1px;
        }

        .nav-link {
            color: #d9d9d9 !important;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 5px;
        }

        .nav-link:hover {
            color: #FEC700 !important;
        }

        .nav-link.active {
            color: #FEC700 !important;
            border-bottom: 2px solid #FEC700;
            padding-bottom: 18px;
        }

        .btn-logout {
            background: #FEC700;
            color: #000435 !important;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-logout:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(254, 199, 0, 0.3);
        }

        .chat-container {
            background: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        }

        .chat-message {
            background: #f9f9f9;
            border-left: 4px solid #FEC700;
            padding: 16px;
            margin-bottom: 16px;
            border-radius: 6px;
            transition: all 0.3s ease;
        }

        .chat-message:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transform: translateX(4px);
        }

        .chat-message.bot {
            border-left-color: #28a745;
            background: #e8f5e9;
        }

        .chat-message.user {
            border-left-color: #FEC700;
            background: #fffbf0;
        }

        .message-sender {
            font-weight: 700;
            color: #000435;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .message-sender.bot {
            color: #28a745;
        }

        .message-sender.user {
            color: #FEC700;
        }

        .message-content {
            color: #333;
            line-height: 1.6;
            word-wrap: break-word;
        }

        .message-timestamp {
            font-size: 0.85rem;
            color: #999;
            margin-top: 8px;
        }

        .message-icon {
            display: inline-block;
            margin-right: 8px;
            font-size: 1rem;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state-icon {
            font-size: 3rem;
            color: #ddd;
            margin-bottom: 16px;
        }

        .empty-state-text {
            color: #999;
            font-size: 1.1rem;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .filter-section h6 {
            font-weight: 700;
            margin-bottom: 15px;
            color: #000435;
        }

        .badge-custom {
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .badge-bot {
            background: #e8f5e9;
            color: #28a745;
        }

        .badge-user {
            background: #fffbf0;
            color: #FEC700;
        }

        .chat-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .stat-card h3 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: #FEC700;
        }

        .stat-card p {
            margin: 8px 0 0 0;
            color: #666;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .chat-container {
                padding: 20px;
            }

            .chat-message {
                padding: 12px;
            }

            .message-content {
                font-size: 0.95rem;
            }

            .chat-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">LEE ADMIN</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">
                            Products
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">
                            Orders
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_chats.php' ? 'active' : ''; ?>" href="admin_chats.php">
                            Chat Messages
                        </a>
                    </li>
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

    <!-- Admin Header -->
    <div class="admin-header">
        <div class="container">
            <h1 class="admin-title">Chat Messages</h1>
            <p class="mb-0">Monitor and manage customer chat interactions</p>
        </div>
    </div>

    <div class="container py-4">
        <!-- Chat Stats -->
        <div class="chat-stats">
            <div class="stat-card">
                <h3>0</h3>
                <p>Total Messages</p>
            </div>
            <div class="stat-card">
                <h3>0</h3>
                <p>User Messages</p>
            </div>
            <div class="stat-card">
                <h3>0</h3>
                <p>Bot Responses</p>
            </div>
            <div class="stat-card">
                <h3>0</h3>
                <p>Active Chats</p>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="filter-section">
            <h6><i class="fas fa-filter me-2"></i>Filter Messages</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <select class="form-select" id="filterType">
                        <option value="">All Messages</option>
                        <option value="user">User Messages</option>
                        <option value="bot">Bot Responses</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="date" class="form-control" id="filterDate" placeholder="Filter by date">
                </div>
                <div class="col-md-4">
                    <button class="btn btn-warning w-100" onclick="clearFilters()">
                        <i class="fas fa-redo me-2"></i>Clear Filters
                    </button>
                </div>
            </div>
        </div>

        <!-- Chat Messages Container -->
        <div class="chat-container">
            <h4 class="mb-4"><i class="fas fa-comments me-2"></i>Message History</h4>
            
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">
                    <i class="fas fa-inbox"></i>
                </div>
                <p class="empty-state-text">No chat messages to display yet.</p>
                <p class="text-muted">Chat messages from customers will appear here once they start conversations.</p>
            </div>

            <!-- Messages will be displayed here when implemented with database -->
            <div id="messagesContainer"></div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function clearFilters() {
            document.getElementById('filterType').value = '';
            document.getElementById('filterDate').value = '';
            // You can add filter logic here when database integration is done
        }
    </script>
</body>
</html>