<?php
require_once '../config/database.php';

// Define $is_logged_in before it's used in the HTML/JavaScript sections
$is_logged_in = isLoggedIn();

if (!$is_logged_in) {
    redirect('../index.php');
}

// Redirect admins to admin panel
if (isAdmin()) {
    redirect('../admin/index.php');
}

$user_id = $_SESSION['user_id'];

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    redirect('index.php');
}

// Get order details
$order_query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($order_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    redirect('index.php');
}

// Get order items
$items_query = "SELECT oi.*, p.name, p.image, pv.image as variant_image
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN product_variants pv ON oi.variant_id = pv.id
                WHERE oi.order_id = ?";
$stmt = $conn->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order_items = $stmt->get_result();
$stmt->close();

$shipping_info = json_decode($order['shipping_address'], true);

// --- FETCH BRANDS FOR NAVBAR ---
$brands_query = "SELECT DISTINCT name FROM brands ORDER BY name ASC";
$brands_result = $conn->query($brands_query);
$brands = [];
while ($brand_row = $brands_result->fetch_assoc()) {
    $brands[] = $brand_row['name'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success - LEE Sneakers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* --- NAVBAR STYLES --- */
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
            /* Adjusted: Removed negative left to fix spacing issues */
            /* left: -40%; REMOVED */
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

        /* Dropdown on Hover - Desktop only */
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
       
        /* --- PAGE SPECIFIC STYLES --- */
        body {
            padding-top: 100px;
            background-color: #f8f9fa;
        }

        .receipt-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
        }
        .receipt-header {
            text-align: center;
            padding-bottom: 30px;
            border-bottom: 2px dashed #ddd;
            margin-bottom: 30px;
        }
        .success-icon {
            font-size: 4rem;
            color: #28a745;
            margin-bottom: 20px;
        }
        .order-status {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        .status-pending {
            background: #ffc107;
            color: #000;
        }
        .info-row {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .item-row {
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .item-row img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        @media print {
            @page {
                size: 140mm auto; 
                margin: 0mm;  
            }

            html, body {
                height: auto;
                margin: 0;
                padding: 0;
                background: #fff;
                visibility: hidden; 
                overflow: visible; 
            }
            
            .receipt-container {
                visibility: visible;
                position: relative;
                width: 100%; 
                height: auto; 
                margin: 0; 
                padding: 10px !important;
                border: 1px solid #000;
                box-shadow: none;
                font-size: 11px;
                line-height: 1.2;
                display: block;
            }

            .receipt-container * {
                visibility: visible;
            }
            
            .receipt-header {
                border-bottom: 1px solid #000;
                padding-bottom: 5px;
                margin-bottom: 5px;
                text-align: center;
            }
            .receipt-header h2 {
                font-size: 14px;
                margin: 0;
                font-weight: bold;
            }
            
            .success-icon, 
            .order-status, 
            .no-print, 
            .alert,
            .item-row img 
            {
                display: none !important;
            }

            h5 {
                font-size: 12px;
                margin: 8px 0 2px 0;
                border-bottom: 1px dashed #ccc;
                padding-bottom: 2px;
            }

            .info-row, .item-row {
                padding: 2px 0;
                border-bottom: none;
            }
            p { margin-bottom: 0; }
            
            .row {
                display: flex;
                flex-wrap: nowrap;
            }
            .col-6 { width: 50%; }
            .col-2 { width: 15%; }
            .col-5 { width: 45%; }
            .col-3 { width: 25%; text-align: right; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg fixed-top no-print">
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
              FOR MEN
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="index.php#basketball-shoes">BASKETBALL SHOES</a></li>
              <li><a class="dropdown-item" href="index.php#running-shoes-men">RUNNING SHOES</a></li>
              <li><a class="dropdown-item" href="index.php#lifestyle-men">LIFESTYLE</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="womenDropdown" role="button" data-bs-toggle="dropdown">
              FOR WOMEN
            </a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="index.php#running-shoes-women">RUNNING SHOES</a></li>
              <li><a class="dropdown-item" href="index.php#lifestyle-women">LIFESTYLE</a></li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="index.php#kids">FOR KIDS</a>
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

    <div class="offcanvas offcanvas-start no-print" tabindex="-1" id="mobileMenu">
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
                                MEN
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
                                WOMEN
                            </button>
                        </h2>
                        <div id="womenCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion">
                            <div class="accordion-body p-0">
                                <a class="nav-link ps-4" href="index.php#running-shoes-women" data-bs-dismiss="offcanvas">RUNNING SHOES</a>
                                <a class="nav-link ps-4" href="index.php#lifestyle-women" data-bs-dismiss="offcanvas">LIFESTYLE</a>
                            </div>
                        </div>
                    </div>
                    
                    <a class="nav-link" href="index.php#kids" data-bs-dismiss="offcanvas">KIDS</a>
                    
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
        <div class="receipt-container" id="receipt">
            <div class="receipt-header">
                <i class="fas fa-check-circle success-icon"></i>
                <h2 class="mb-3">Order Placed Successfully!</h2>
                <p class="text-muted mb-3">Thank you for your order. We'll process it shortly.</p>
                <span class="order-status status-pending">
                    <i class="fas fa-clock me-2"></i>Pending Confirmation
                </span>
            </div>

            <div class="mb-4">
                <h5 class="mb-3"><i class="fas fa-receipt me-2"></i>Order Details</h5>
                <div class="info-row">
                    <div class="row">
                        <div class="col-6"><strong>Order ID:</strong></div>
                        <div class="col-6 text-end">#<?php echo $order['id']; ?></div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="row">
                        <div class="col-6"><strong>Order Date:</strong></div>
                        <div class="col-6 text-end"><?php echo date('F d, Y h:i A', strtotime($order['created_at'])); ?></div>
                    </div>
                </div>
                <div class="info-row">
                    <div class="row">
                        <div class="col-6"><strong>Payment Method:</strong></div>
                        <div class="col-6 text-end"><?php echo $order['payment_method']; ?></div>
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <h5 class="mb-3"><i class="fas fa-user me-2"></i>Customer Information</h5>
                <p class="mb-1"><strong>Name:</strong> <?php echo htmlspecialchars($shipping_info['full_name']); ?></p>
                <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($shipping_info['email']); ?></p>
                <p class="mb-1"><strong>Phone:</strong> <?php echo htmlspecialchars($shipping_info['phone']); ?></p>
            </div>

            <div class="mb-4">
                <h5 class="mb-3"><i class="fas fa-map-marker-alt me-2"></i>Shipping Address</h5>
                <p class="mb-0">
                    <?php echo htmlspecialchars($shipping_info['street']); ?><br>
                    <?php echo htmlspecialchars($shipping_info['barangay']); ?>, 
                    <?php echo htmlspecialchars($shipping_info['city']); ?><br>
                    <?php echo htmlspecialchars($shipping_info['province']); ?> 
                    <?php echo htmlspecialchars($shipping_info['postal_code']); ?>
                </p>
            </div>

            <div class="mb-4">
                <h5 class="mb-3"><i class="fas fa-shopping-bag me-2"></i>Order Items</h5>
                <?php while ($item = $order_items->fetch_assoc()): ?>
                    <div class="item-row">
                        <div class="row align-items-center">
                            <div class="col-2">
                                <img src="../uploads/products/<?php echo $item['variant_image']; ?>" alt="">
                            </div>
                            <div class="col-5">
                                <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                <small class="text-muted">Size: <?php echo $item['size']; ?></small>
                            </div>
                            <div class="col-2 text-center">
                                <small class="text-muted">Qty:</small><br>
                                <strong><?php echo $item['quantity']; ?></strong>
                            </div>
                            <div class="col-3 text-end">
                                <strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <div class="mb-4">
                <div class="row">
                    <div class="col-6 offset-6">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong><?php echo formatPrice($order['total_amount']); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <strong class="text-success">FREE</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h5>Total:</h5>
                            <h5 class="text-primary"><?php echo formatPrice($order['total_amount']); ?></h5>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning">
                <h6><i class="fas fa-info-circle me-2"></i>Important Notice</h6>
                <ul class="mb-0">
                    <li>Your order is currently <strong>pending confirmation</strong></li>
                    <li>Our team will review and confirm your order within 24 hours</li>
                    <li>You will receive an update once your order status changes</li>
                    <li>Please prepare the exact amount for cash on delivery</li>
                </ul>
            </div>

            <div class="text-center mt-4 no-print">
                <button onclick="window.print()" class="btn btn-outline-primary me-2">
                    <i class="fas fa-print me-2"></i>Print Receipt
                </button>
                <a href="my_orders.php" class="btn btn-outline-secondary me-2">
                    <i class="fas fa-list me-2"></i>View All Orders
                </a>
                <a href="index.php" class="btn btn-dark">
                    <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                </a>
            </div>
        </div>
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