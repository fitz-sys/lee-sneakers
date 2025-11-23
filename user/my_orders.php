<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('../index.php');
}

// Redirect admins to admin panel
if (isAdmin()) {
    redirect('../admin/index.php');
}

$user_id = $_SESSION['user_id'];
$is_logged_in = isLoggedIn();

// --- FETCH BRANDS FOR NAVBAR (Added to match index.php) ---
$brands_query = "SELECT DISTINCT name FROM brands ORDER BY name ASC";
$brands_result = $conn->query($brands_query);
$brands = [];
while ($brand_row = $brands_result->fetch_assoc()) {
    $brands[] = $brand_row['name'];
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - LEE Sneakers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* --- NAVBAR STYLES (Copied & Adjusted from index.php) --- */
        .navbar {
            background: #000435;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
            padding: 0.8rem 0;
        }
       
        .navbar-brand {
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1050;
        }
       
        .navbar-brand img {
            height: 65px;
            width: auto;
        }
       
        .navbar-nav {
            align-items: center;
            gap: 0rem;
        }
       
        .navbar-nav .nav-link {
            /* REMOVED 'left: -40%' to fix spacing issue */
            color: #FEC700;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
            margin: 0 1px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
        }

        .navbar-nav .nav-item:has(#womenDropdown) {
            margin-left: 1rem;
        }

        .navbar-nav .nav-item:has([href="#brands"]) {
            margin-left: -1rem;
        }
       
        .navbar-nav .nav-link:hover {
            color: #fff;
            background: rgba(254, 199, 0, 0.1);
            border-radius: 4px;
        }
       
        /* Dropdown Menu Styles */
        .navbar-nav .dropdown-menu {
            background: #000435;
            border: 2px solid #FEC700;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            border-radius: 0;
            padding: 0;
            margin-top: 0;
            min-width: 200px;
        }
       
        .navbar-nav .dropdown-item {
            padding: 1rem 1.5rem;
            font-weight: 500;
            color: #FEC700;
            transition: all 0.3s ease;
            border-bottom: 1px solid rgba(254, 199, 0, 0.2);
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
       
        .navbar-nav .dropdown-item:last-child {
            border-bottom: none;
        }
       
        .navbar-nav .dropdown-item:hover {
            background: #FEC700;
            color: #000435;
            padding-left: 2rem;
        }

        .navbar-nav .dropdown-menu.show {
            z-index: 1050;
        }

        @media (min-width: 992px) {
            .navbar-nav .dropdown:hover .dropdown-menu {
                display: block;
                margin-top: 0;
            }
            
            .navbar-nav .dropdown-menu {
                transition: all 0.3s ease;
            }
        }
       
        .nav-icons {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
       
        .nav-icons a {
            color: #FEC700;
            font-size: 1.3rem;
            transition: all 0.3s ease;
            cursor: pointer;
        }
       
        .nav-icons a:hover, .nav-icons i:hover {
            color: #fff;
            transform: scale(1.1);
        }
       
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #FEC700;
            color: #000435;
            border-radius: 50%;
            padding: 3px 7px;
            font-size: 0.7rem;
            font-weight: bold;
            border: 2px solid #000435;
        }

        /* Profile Dropdown */
        .profile-container {
            position: relative;
        }

        .profile-dropdown {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            background: white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            border-radius: 6px;
            padding: 0.3rem 0;
            min-width: 180px;
            display: none;
            z-index: 1000;
        }

        .profile-dropdown.show {
            display: block;
        }

        .profile-dropdown a {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.5rem 1rem;
            color: #333;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .profile-dropdown a:hover {
            background: #FEC700;
            color: #000;
        }

        .profile-dropdown .dropdown-divider {
            margin: 0.4rem 0;
            border-top: 1px solid #eee;
        }

        /* Mobile Menu Styles */
        .navbar-toggler {
            border: 2px solid #FEC700;
            padding: 0.5rem;
        }
       
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='%23FEC700' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
       
        .navbar-toggler:focus {
            box-shadow: 0 0 0 0.25rem rgba(254, 199, 0, 0.25);
        }
       
        .offcanvas {
            background: #000435;
        }
       
        .offcanvas-header {
            background: #000435;
            color: #FEC700;
            border-bottom: 3px solid #FEC700;
        }
       
        .offcanvas-title {
            font-weight: 700;
            letter-spacing: 2px;
        }
        
        .btn-close-white {
            filter: brightness(0) saturate(100%) invert(84%) sepia(47%) saturate(834%) hue-rotate(357deg) brightness(103%) contrast(101%);
        }
       
        .mobile-menu-section {
            border-bottom: 1px solid rgba(254, 199, 0, 0.2);
        }
       
        .mobile-menu-section .nav-link {
            padding: 1rem 1.5rem;
            color: #FEC700;
            border-bottom: 1px solid rgba(254, 199, 0, 0.1);
            transition: all 0.3s ease;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
       
        .mobile-menu-section .nav-link:hover {
            background: rgba(254, 199, 0, 0.1);
            padding-left: 2rem;
            color: #fff;
        }
       
        .accordion-button {
            background: #000435;
            color: #FEC700;
            border: none;
            padding: 1rem 1.5rem;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.5px;
        }
       
        .accordion-button:not(.collapsed) {
            background: rgba(254, 199, 0, 0.1);
            color: #fff;
            box-shadow: none;
        }

        .accordion-button::after {
            filter: brightness(0) saturate(100%) invert(84%) sepia(47%) saturate(834%) hue-rotate(357deg) brightness(103%) contrast(101%);
        }
       
        .accordion-item {
            background: #000435;
            border: none;
            border-bottom: 1px solid rgba(254, 199, 0, 0.1);
        }
       
        .accordion-body {
            background: #000435;
        }

        @media (max-width: 991px) {
            .navbar-brand img {
                height: 50px;
            }
           
            .navbar-toggler {
                order: 1;
            }
           
            .nav-icons {
                order: 3;
                margin-left: auto;
            }
        }

        /* --- ORDER PAGE SPECIFIC STYLES --- */
        body {
            padding-top: 100px;
            background-color: #f8f9fa;
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
        .empty-orders {
            text-align: center;
            padding: 60px 20px;
        }
        .empty-orders i {
            font-size: 5rem;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container-fluid">
   
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <a class="navbar-brand" href="index.php">
      <img src="../nav_images/logo.png" alt="LEE Sneakers Logo">
    </a>

    <ul class="navbar-nav d-none d-lg-flex flex-row align-items-center ms-5" style="margin-left: 60px;">
      <li class="nav-item">
        <a class="nav-link" href="index.php#best-seller">BEST SELLER</a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="menDropdown" role="button" data-bs-toggle="dropdown">
          MEN
        </a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="index.php#basketball-shoes">BASKETBALL SHOES</a></li>
          <li><a class="dropdown-item" href="index.php#running-shoes-men">RUNNING SHOES</a></li>
          <li><a class="dropdown-item" href="index.php#lifestyle-men">LIFESTYLE</a></li>
        </ul>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="womenDropdown" role="button" data-bs-toggle="dropdown">
          WOMEN
        </a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="index.php#running-shoes-women">RUNNING SHOES</a></li>
          <li><a class="dropdown-item" href="index.php#lifestyle-women">LIFESTYLE</a></li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="index.php#kids">KIDS</a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="brandsDropdown" role="button" data-bs-toggle="dropdown">
          BRANDS
        </a>
        <ul class="dropdown-menu">
          <?php foreach ($brands as $brand): 
            $brand_slug = strtolower(str_replace(' ', '', $brand));
          ?>
          <li><a class="dropdown-item" href="index.php#brand-<?php echo $brand_slug; ?>"><?php echo strtoupper($brand); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </li>
    </ul>

    <div class="nav-icons ms-auto">
      <div class="profile-container">
            <i class="fas fa-user nav-icon" onclick="toggleProfile()"></i>
            <?php if ($is_logged_in): ?>
            <div class="profile-dropdown" id="profileDropdown">
                <a href="profile.php"><i class="fas fa-user me-2"></i>View Profile</a>
                <a href="my_orders.php"><i class="fas fa-shopping-bag me-2"></i>Order History</a>
                <div class="dropdown-divider"></div>
                <a href="../includes/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Log Out</a>
            </div>
            <?php endif; ?>
        </div>
     
      <a href="cart.php" title="Cart" style="position: relative;">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($is_logged_in): ?>
        <span class="cart-badge" id="cartBadge" style="display: none;">0</span>
        <?php endif; ?>
      </a>
    </div>
  </div>
</nav>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">MENU</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mobile-menu-section">
            <a class="nav-link" href="index.php#best-seller" data-bs-dismiss="offcanvas">BEST SELLER</a>
        
            <div class="accordion accordion-flush" id="mobileAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menCollapse">
                            FOR MEN
                        </button>
                    </h2>
                    <div id="menCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion">
                        <div class="accordion-body p-0">
                            <a class="nav-link ps-4" href="index.php#basketball-shoes" data-bs-dismiss="offcanvas">BASKETBALL SHOES</a>
                            <a class="nav-link ps-4" href="index.php#running-shoes-men" data-bs-dismiss="offcanvas">RUNNING SHOES</a>
                            <a class="nav-link ps-4" href="index.php#lifestyle-men" data-bs-dismiss="offcanvas">LIFESTYLE</a>
                        </div>
                    </div>
                </div>
            
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#womenCollapse">
                            FOR WOMEN
                        </button>
                    </h2>
                    <div id="womenCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion">
                        <div class="accordion-body p-0">
                            <a class="nav-link ps-4" href="index.php#running-shoes-women" data-bs-dismiss="offcanvas">RUNNING SHOES</a>
                            <a class="nav-link ps-4" href="index.php#lifestyle-women" data-bs-dismiss="offcanvas">LIFESTYLE</a>
                        </div>
                    </div>
                </div>
                
                <a class="nav-link" href="index.php#kids" data-bs-dismiss="offcanvas">FOR KIDS</a>
                
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#brandsCollapse">
                            BRANDS
                        </button>
                    </h2>
                    <div id="brandsCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion">
                        <div class="accordion-body p-0">
                            <?php foreach ($brands as $brand): 
                                $brand_slug = strtolower(str_replace(' ', '', $brand));
                            ?>
                            <a class="nav-link ps-4" href="index.php#brand-<?php echo $brand_slug; ?>" data-bs-dismiss="offcanvas"><?php echo strtoupper($brand); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        
            <a class="nav-link" href="about.php" data-bs-dismiss="offcanvas">ABOUT US</a>
        </div>
    </div>
</div>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><i class="fas fa-shopping-bag me-2"></i>My Orders</h2>
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Continue Shopping
            </a>
        </div>

        <?php if ($orders->num_rows > 0): ?>
            <?php while ($order = $orders->fetch_assoc()): ?>
                <?php $shipping = json_decode($order['shipping_address'], true); ?>
                <div class="order-card">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between mb-2">
                                <h5 class="mb-0">Order #<?php echo $order['id']; ?></h5>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            <p class="text-muted mb-2">
                                <i class="far fa-calendar me-2"></i>
                                Ordered on <?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?>
                            </p>
                            <p class="mb-2">
                                <i class="fas fa-box me-2"></i>
                                <?php echo $order['item_count']; ?> item(s) • 
                                <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                            </p>
                            <p class="mb-0">
                                <i class="fas fa-credit-card me-2"></i>
                                <?php echo $order['payment_method']; ?>
                            </p>
                        </div>
                        <div class="col-md-4 d-flex align-items-center justify-content-end">
                            <div class="d-grid gap-2 w-100">
                                <a href="view_order_details.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary">
                                    <i class="fas fa-eye me-2"></i>View Details
                                </a>
                                <?php if ($order['status'] === 'completed'): ?>
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="fas fa-check me-2"></i>Order Completed
                                    </button>
                                <?php elseif ($order['status'] === 'cancelled'): ?>
                                    <button class="btn btn-outline-danger" disabled>
                                        <i class="fas fa-times me-2"></i>Order Cancelled
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-outline-secondary" disabled>
                                        <i class="fas fa-clock me-2"></i>In Progress
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($order['status'] === 'pending'): ?>
                        <div class="alert alert-warning mt-3 mb-0">
                            <small>
                                <i class="fas fa-info-circle me-2"></i>
                                Your order is awaiting confirmation from our team.
                            </small>
                        </div>
                    <?php elseif ($order['status'] === 'confirmed'): ?>
                        <div class="alert alert-info mt-3 mb-0">
                            <small>
                                <i class="fas fa-check-circle me-2"></i>
                                Your order has been confirmed and will be processed soon.
                            </small>
                        </div>
                    <?php elseif ($order['status'] === 'processing'): ?>
                        <div class="alert alert-primary mt-3 mb-0">
                            <small>
                                <i class="fas fa-shipping-fast me-2"></i>
                                Your order is being prepared for delivery.
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-orders">
                <i class="fas fa-shopping-bag"></i>
                <h3>No orders yet</h3>
                <p class="text-muted mb-4">You haven't placed any orders. Start shopping now!</p>
                <a href="index.php" class="btn btn-dark btn-lg">
                    <i class="fas fa-shopping-cart me-2"></i>Start Shopping
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

        function toggleProfile() {
            if (!isLoggedIn) {
                // openLoginModal()
                return;
            }
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.profile-container')) {
                const dropdown = document.getElementById('profileDropdown');
                if (dropdown) {
                    dropdown.classList.remove('show');
                }
            }
        });

        // Prevent dropdown toggle links from doing anything
        document.querySelectorAll('#menDropdown, #womenDropdown, #brandsDropdown').forEach(dropdownToggle => {
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
            });
        });

        function updateCartBadge() {
            if (!isLoggedIn) return;
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    const badge = document.getElementById('cartBadge');
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        window.addEventListener('load', function() {
            updateCartBadge();
        });
    </script>
</body>
</html>