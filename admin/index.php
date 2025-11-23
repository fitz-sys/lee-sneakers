<?php
require_once '../config/database.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect(url: '../index.php');
}

// Get statistics
$total_products = $conn->query("SELECT COUNT(*) as count FROM products")->fetch_assoc()['count'];
$total_users = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'user'")->fetch_assoc()['count'];
$total_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$total_sales = $conn->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM orders WHERE status = 'completed'")->fetch_assoc()['total'];

// Get recent orders
$recent_orders_query = "SELECT o.*, u.username, u.full_name 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.id 
                        ORDER BY o.created_at DESC 
                        LIMIT 10";
$recent_orders = $conn->query($recent_orders_query);

// Get sales data for chart (last 7 days)
$sales_data = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $query = "SELECT COALESCE(SUM(total_amount), 0) as total 
              FROM orders 
              WHERE DATE(created_at) = '$date' AND status = 'completed'";
    $result = $conn->query($query)->fetch_assoc();
    $sales_data[] = [
        'date' => date('M d', strtotime($date)),
        'total' => $result['total']
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LEE Sneakers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
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
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['products.php', 'add_product.php', 'edit_product.php']) ? 'active' : ''; ?>" href="products.php">
                        Products
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['orders.php', 'view_order.php']) ? 'active' : ''; ?>" href="orders.php">
                        Orders
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo in_array(basename($_SERVER['PHP_SELF']), ['users.php', 'view_user.php']) ? 'active' : ''; ?>" href="users.php">
                        Users
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
            <h1 class="admin-title">Dashboard</h1>
            <p class="mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['full_name']); ?></p>
        </div>
    </div>

    <div class="container py-5">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon bg-primary">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $total_products; ?></h3>
                        <p>Total Products</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon bg-success">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $total_users; ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon bg-warning">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo $total_orders; ?></h3>
                        <p>Total Orders</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon bg-danger">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stats-info">
                        <h3><?php echo formatPrice($total_sales); ?></h3>
                        <p>Total Sales</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales Chart -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="admin-card">
                    <h4 class="mb-4">Sales Overview (Last 7 Days)</h4>
                    <canvas id="salesChart"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="admin-card">
                    <h4 class="mb-4">Quick Actions</h4>
                    <div class="d-grid gap-2">
                        <a href="add_product.php" class="btn btn-add-product">
                            <i class="fas fa-plus me-2"></i>Add New Product
                        </a>
                        <a href="products.php" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-2"></i>View All Products
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="admin-card">
            <h4 class="mb-4">Recent Orders</h4>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($recent_orders->num_rows > 0): ?>
                            <?php while ($order = $recent_orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $order['status'] === 'completed' ? 'success' : 
                                                ($order['status'] === 'pending' ? 'warning' : 'secondary'); 
                                        ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No orders yet</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Chat Widget CSS -->
    <style>
        .chatbot-widget {
            position: fixed;
            bottom: 100px;
            right: 30px;
            z-index: 9999;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            transition: all 0.3s ease;
        }

        .chat-bubble-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #FEC700;
            color: #000435;
            border: none;
            cursor: pointer;
            font-size: 24px;
            box-shadow: 0 4px 12px rgba(254, 199, 0, 0.4);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .chat-bubble-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 16px rgba(254, 199, 0, 0.6);
        }

        .chat-bubble-btn.active {
            display: flex;
            background: #000435;
            color: #FEC700;
            transform: rotate(90deg);
        }

        .chat-window {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 380px;
            height: 600px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            overflow: hidden;
            animation: slideUp 0.3s ease;
            z-index: 1040;
            max-height: calc(100vh - 150px);
        }

        .chat-window.active {
            display: flex;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .chat-header {
            background: #FEC700;
            color: #000435;
            padding: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #f0c800;
            gap: 12px;
            flex-shrink: 0;
        }

        .chat-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            flex: 1;
            text-align: center;
        }

        .chat-close-btn {
            background: none;
            border: none;
            color: #000435;
            font-size: 20px;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .chat-close-btn:hover {
            transform: scale(1.2);
        }

        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            background: #f9f9f9;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .message {
            display: flex;
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .message.bot {
            justify-content: flex-start;
        }

        .message.user {
            justify-content: flex-end;
        }

        .message-content {
            max-width: 70%;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.4;
            word-wrap: break-word;
        }

        .bot .message-content {
            background: #e8e8e8;
            color: #333;
        }

        .user .message-content {
            background: #FEC700;
            color: #000435;
            font-weight: 500;
        }

        .chat-input-section {
            padding: 12px;
            background: white;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 8px;
            flex-shrink: 0;
        }

        .chat-input {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 6px;
            padding: 10px 12px;
            font-size: 13px;
            outline: none;
            transition: border-color 0.2s;
        }

        .chat-input:focus {
            border-color: #FEC700;
        }

        .chat-send-btn {
            background: #FEC700;
            color: #000435;
            border: none;
            border-radius: 6px;
            padding: 10px 14px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.2s;
        }

        .chat-send-btn:hover {
            background: #f0c800;
            transform: translateY(-2px);
        }
    </style>
    
    <script>
        // Chat variables
        const adminUsername = '<?php echo $_SESSION['username']; ?>';
        const isAdminUser = true;

        // Add Message to Chat
        function addMessage(text, sender) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;

            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.innerHTML = text.replace(/\n/g, '<br>');

            messageDiv.appendChild(contentDiv);
            chatMessages.appendChild(messageDiv);

            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Toggle Chat
        function toggleChat() {
            const chatWindow = document.getElementById('chatWindow');
            const chatBubbleBtn = document.getElementById('chatBubbleBtn');
            
            if (chatWindow.classList.contains('active')) {
                chatWindow.classList.remove('active');
                chatBubbleBtn.classList.remove('active');
            } else {
                chatWindow.classList.add('active');
                chatBubbleBtn.classList.add('active');
                fetchChatMessages();
            }
        }

        // Fetch Chat Messages
        function fetchChatMessages() {
            const selectedUser = document.getElementById('userSelector').value;
            
            // If no user selected, fetch all messages and populate user selector
            if (!selectedUser) {
                fetch('../chatbot/api.php?action=get_admin_messages&username=' + encodeURIComponent(adminUsername))
                    .then(response => response.json())
                    .then(data => {
                        console.log('Chat messages:', data);
                        if (data.success && data.messages) {
                            // Extract unique usernames (except admin)
                            const uniqueUsers = new Set();
                            data.messages.forEach(msg => {
                                if (msg.username !== adminUsername) {
                                    uniqueUsers.add(msg.username);
                                } else if (msg.receiver !== adminUsername) {
                                    uniqueUsers.add(msg.receiver);
                                }
                            });
                            
                            // Populate user selector
                            const userSelector = document.getElementById('userSelector');
                            userSelector.innerHTML = '<option value="">-- Select a user to chat with --</option>';
                            uniqueUsers.forEach(user => {
                                const option = document.createElement('option');
                                option.value = user;
                                option.textContent = user;
                                userSelector.appendChild(option);
                            });
                            
                            // Show welcome message if no user selected
                            const chatMessages = document.getElementById('chatMessages');
                            chatMessages.innerHTML = '<div class="message bot"><div class="message-content"><p>Select a user from the dropdown to view and chat with them.</p></div></div>';
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching chat messages:', error);
                    });
            } else {
                // Load messages for selected user
                fetch('../chatbot/api.php?action=get_admin_messages&username=' + encodeURIComponent(adminUsername))
                    .then(response => response.json())
                    .then(data => {
                        console.log('Chat messages:', data);
                        if (data.success && data.messages) {
                            const chatMessages = document.getElementById('chatMessages');
                            chatMessages.innerHTML = '';
                            
                            // Filter messages for selected user
                            const filteredMessages = data.messages.filter(msg => {
                                return (msg.username === selectedUser || msg.receiver === selectedUser);
                            });
                            
                            filteredMessages.forEach(msg => {
                                const isSentMessage = msg.username === adminUsername;
                                const messageType = isSentMessage ? 'user' : 'bot';
                                let labeledMessage = msg.chat;
                                
                                if (!isSentMessage) {
                                    labeledMessage = labeledMessage;
                                }
                                
                                addMessage(labeledMessage, messageType);
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching chat messages:', error);
                    });
            }
        }

        // Close Chat
        function closeChat() {
            const chatWindow = document.getElementById('chatWindow');
            const chatBubbleBtn = document.getElementById('chatBubbleBtn');
            
            chatWindow.classList.remove('active');
            chatBubbleBtn.classList.remove('active');
        }

        // Send Message
        function sendMessage() {
            const input = document.getElementById('chatInput');
            const selectedUser = document.getElementById('userSelector').value;
            const message = input.value.trim();

            if (!message) {
                alert('Please type a message');
                return;
            }

            if (!selectedUser) {
                alert('Please select a user to chat with');
                return;
            }

            fetch('../chatbot/api.php?action=save_admin_message', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    username: adminUsername,
                    chat: message,
                    receiver: selectedUser
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log('Message saved:', data);
                if (data.success) {
                    addMessage(message, 'user');
                    input.value = '';
                }
            })
            .catch(error => {
                console.error('Error saving message:', error);
            });
        }

        // Handle Enter Key
        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }

        // Initialize user selector listener
        document.addEventListener('DOMContentLoaded', function() {
            const userSelector = document.getElementById('userSelector');
            if (userSelector) {
                userSelector.addEventListener('change', function() {
                    fetchChatMessages();
                });
            }
        });
    </script>
    
    <script>
        // Sales Chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesData = <?php echo json_encode($sales_data); ?>;
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: salesData.map(d => d.date),
                datasets: [{
                    label: 'Sales (₱)',
                    data: salesData.map(d => d.total),
                    borderColor: '#FEC700',
                    backgroundColor: 'rgba(254, 199, 0, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    
    <!-- Chat Widget HTML -->
    <div class="chatbot-widget">
        <button class="chat-bubble-btn" id="chatBubbleBtn" onclick="toggleChat()">
            <i class="fas fa-comments"></i>
        </button>

        <div class="chat-window" id="chatWindow">
            <div class="chat-header">
                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <h3 style="margin: 0;">Chat with User</h3>
                    <button class="chat-close-btn" onclick="closeChat()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <select id="userSelector" style="width: 100%; padding: 8px; margin-top: 10px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="">-- Select a user to chat with --</option>
                </select>
            </div>

            <div class="chat-messages" id="chatMessages">
                <div class="message bot">
                    <div class="message-content">
                        <p>Welcome to Admin Chat</p>
                    </div>
                </div>
            </div>

            <div class="chat-input-section">
                <input 
                    type="text" 
                    class="chat-input" 
                    id="chatInput" 
                    placeholder="Type your message..." 
                    onkeypress="handleKeyPress(event)"
                >
                <button class="chat-send-btn" onclick="sendMessage()">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>
</body>
</html>