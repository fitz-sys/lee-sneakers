<?php
require_once '../config/database.php';

// Check if user is logged in
$is_logged_in = isLoggedIn();
$username = $is_logged_in ? $_SESSION['username'] : '';

// --- FETCH BRANDS FOR NAVBAR & FOOTER (Added to match index.php) ---
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
    <title>Shipping Policy - LEE Sneakers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f8f9fa; /* Preserved original background */
            font-family: 'Poppins', sans-serif;
            padding-top: 100px; /* Added for fixed navbar */
        }

        /* --- NAVBAR STYLES (Identical to index.php) --- */
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
            margin: 0;
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

        .navbar-nav .nav-item:has([href*="brands"]) {
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

        /* --- SHIPPING POLICY SPECIFIC STYLES --- */
        .page-header {
            background: linear-gradient(135deg, #000435 0%, #064734 100%);
            color: white;
            padding: 80px 0 60px;
            text-align: center;
            margin-bottom: 50px;
        }

        .page-header h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
            color: #FEC700;
        }

        .page-header p {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.9);
        }

        .content-section {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 80px;
        }

        .policy-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .policy-card h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #000435;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 3px solid #FEC700;
        }

        .policy-card h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #064734;
            margin: 25px 0 15px;
        }

        .policy-card p {
            color: #555;
            line-height: 1.8;
            margin-bottom: 15px;
        }

        .policy-card ul {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }

        .policy-card ul li {
            padding: 10px 0;
            padding-left: 30px;
            position: relative;
            color: #555;
            line-height: 1.6;
        }

        .policy-card ul li:before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            left: 0;
            color: #FEC700;
        }

        .highlight-box {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .highlight-box i {
            color: #ffc107;
            font-size: 1.5rem;
            margin-right: 10px;
        }

        .shipping-table {
            width: 100%;
            margin: 25px 0;
            border-collapse: collapse;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .shipping-table th {
            background: linear-gradient(135deg, #000435 0%, #064734 100%);
            color: #FEC700;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }

        .shipping-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            color: #555;
        }

        .shipping-table tr:last-child td {
            border-bottom: none;
        }

        .shipping-table tr:hover {
            background: #f8f9fa;
        }

        .btn-back {
            background: #000435;
            color: #FEC700;
            border: none;
            padding: 15px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 30px;
        }

        .btn-back:hover {
            background: #FEC700;
            color: #000435;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(254, 199, 0, 0.3);
        }

        /* ===== FOOTER SECTION (Identical to index.php) ===== */
        .footer-section {
            background: #064734; /* dark green */
            color: #ffffff;
            padding: 60px 0 25px;
            margin-top: 80px;
            font-family: 'Poppins', sans-serif;
        }

        .footer-title {
            color: #FEC700; /* yellow */
            font-weight: 700;
            font-size: 1rem;
            margin-bottom: 18px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .footer-text {
            color: #d9d9d9;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 20px;
            max-width: 300px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #d9d9d9;
            font-size: 0.9rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            color: #FEC700;
            text-decoration: underline;
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .social-icons a {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background: #FEC700;
            border-radius: 50%;
            color: #000435 !important;
            font-size: 1.25rem;
            transition: all 0.3s ease;
            text-decoration: none;
            vertical-align: middle;
        }

        .social-icons a i {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #000435 !important;
            font-size: 1.25rem;
            line-height: 1;
        }

        .social-icons a:hover {
            background: #000435;
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(254, 199, 0, 0.4);
        }

        .social-icons a:hover i {
            color: #FEC700 !important;
        }

        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: 40px;
            padding-top: 20px;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.7);
            text-align: center;
        }

        @media (max-width: 768px) {
            .footer-section {
                padding: 40px 0 20px;
                margin-top: 50px;
            }
            .footer-title {
                font-size: 0.95rem;
                margin-bottom: 15px;
                text-align: center;
            }
            .footer-text {
                text-align: center;
                margin: 0 auto 20px;
            }
            .footer-links {
                text-align: center;
            }
            .social-icons {
                justify-content: center;
            }
            .footer-bottom {
                font-size: 0.8rem;
            }
            .page-header h1 {
                font-size: 2rem;
            }
            .policy-card {
                padding: 25px;
            }
            .shipping-table {
                font-size: 0.9rem;
            }
            .shipping-table th, .shipping-table td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
  <div class="container-fluid">
   
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <a class="navbar-brand" href="../user/index.php">
      <img src="../nav_images/logo.png" alt="LEE Sneakers Logo">
    </a>

    <ul class="navbar-nav d-none d-lg-flex flex-row align-items-center ms-5" style="margin-left: 60px;">
      <li class="nav-item">
        <a class="nav-link" href="../user/index.php#best-seller">BEST SELLER</a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="menDropdown" role="button" data-bs-toggle="dropdown">
          MEN
        </a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="../user/index.php#basketball-shoes">BASKETBALL SHOES</a></li>
          <li><a class="dropdown-item" href="../user/index.php#running-shoes-men">RUNNING SHOES</a></li>
          <li><a class="dropdown-item" href="../user/index.php#lifestyle-men">LIFESTYLE</a></li>
        </ul>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="womenDropdown" role="button" data-bs-toggle="dropdown">
          WOMEN
        </a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="../user/index.php#running-shoes-women">RUNNING SHOES</a></li>
          <li><a class="dropdown-item" href="../user/index.php#lifestyle-women">LIFESTYLE</a></li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="../user/index.php#kids">KIDS</a>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="brandsDropdown" role="button" data-bs-toggle="dropdown">
          BRANDS
        </a>
        <ul class="dropdown-menu">
          <?php foreach ($brands as $brand): 
            $brand_slug = strtolower(str_replace(' ', '', $brand));
          ?>
          <li><a class="dropdown-item" href="../user/index.php#brand-<?php echo $brand_slug; ?>"><?php echo strtoupper($brand); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </li>
    </ul>

    <div class="nav-icons ms-auto">
      <div class="profile-container">
            <i class="fas fa-user nav-icon" onclick="toggleProfile()"></i>
            <?php if ($is_logged_in): ?>
            <div class="profile-dropdown" id="profileDropdown">
                <a href="../user/profile.php"><i class="fas fa-user me-2"></i>View Profile</a>
                <a href="../user/my_orders.php"><i class="fas fa-shopping-bag me-2"></i>Order History</a>
                <div class="dropdown-divider"></div>
                <a href="../includes/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Log Out</a>
            </div>
            <?php endif; ?>
        </div>
     
      <a href="../user/cart.php" title="Cart" style="position: relative;">
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
            <a class="nav-link" href="../user/index.php#best-seller" data-bs-dismiss="offcanvas">BEST SELLER</a>
        
            <div class="accordion accordion-flush" id="mobileAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menCollapse">
                            FOR MEN
                        </button>
                    </h2>
                    <div id="menCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion">
                        <div class="accordion-body p-0">
                            <a class="nav-link ps-4" href="../user/index.php#basketball-shoes" data-bs-dismiss="offcanvas">BASKETBALL SHOES</a>
                            <a class="nav-link ps-4" href="../user/index.php#running-shoes-men" data-bs-dismiss="offcanvas">RUNNING SHOES</a>
                            <a class="nav-link ps-4" href="../user/index.php#lifestyle-men" data-bs-dismiss="offcanvas">LIFESTYLE</a>
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
                            <a class="nav-link ps-4" href="../user/index.php#running-shoes-women" data-bs-dismiss="offcanvas">RUNNING SHOES</a>
                            <a class="nav-link ps-4" href="../user/index.php#lifestyle-women" data-bs-dismiss="offcanvas">LIFESTYLE</a>
                        </div>
                    </div>
                </div>
                
                <a class="nav-link" href="../user/index.php#kids" data-bs-dismiss="offcanvas">FOR KIDS</a>
                
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
                            <a class="nav-link ps-4" href="../user/index.php#brand-<?php echo $brand_slug; ?>" data-bs-dismiss="offcanvas"><?php echo strtoupper($brand); ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        
            <a class="nav-link" href="../user/about.php" data-bs-dismiss="offcanvas">ABOUT US</a>
        </div>
    </div>
</div>

<div class="page-header">
    <div class="container">
        <h1><i class="fas fa-shipping-fast me-3"></i>Shipping Policy</h1>
        <p>Fast, Reliable, and Secure Delivery</p>
        <p class="small mt-2">Last Updated: November 2025</p>
    </div>
</div>

<div class="content-section">
    <div class="policy-card">
        <h2>Shipping Information</h2>
        <p>At LEE Sneakers, we are committed to delivering your orders quickly and safely. We currently ship to locations throughout the Philippines. Please read our shipping policy carefully to understand delivery times, costs, and procedures.</p>
        
        <div class="highlight-box">
            <i class="fas fa-info-circle"></i>
            <strong>Coverage Area:</strong> We ship nationwide within the Philippines. International shipping is not currently available.
        </div>

        <h3>Shipping Rates and Delivery Time</h3>
        <p>Our shipping rates are based on your delivery location:</p>
        
        <table class="shipping-table">
            <thead>
                <tr>
                    <th><i class="fas fa-map-marker-alt me-2"></i>Location</th>
                    <th><i class="fas fa-peso-sign me-2"></i>Shipping Fee</th>
                    <th><i class="fas fa-clock me-2"></i>Delivery Time</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Metro Manila</strong></td>
                    <td><strong>FREE</strong></td>
                    <td>1-2 business days</td>
                </tr>
                <tr>
                    <td><strong>Outside Metro Manila</strong></td>
                    <td><strong>₱100.00</strong></td>
                    <td>3-5 business days</td>
                </tr>
            </tbody>
        </table>

        <div class="highlight-box" style="background: #d1ecf1; border-left-color: #0c5460;">
            <i class="fas fa-gift" style="color: #0c5460;"></i>
            <strong>Free Shipping:</strong> Enjoy free delivery for all orders within Metro Manila! Outside Metro Manila, a flat rate of ₱100 applies.
        </div>

        <h3>Order Processing Time</h3>
        <p>Understanding our processing timeline:</p>
        <ul>
            <li><strong>Order Verification:</strong> 1-2 hours after order placement</li>
            <li><strong>Payment Confirmation:</strong> GCash payments verified within 2-4 hours</li>
            <li><strong>Order Preparation:</strong> 1 business day for picking and packing</li>
            <li><strong>Dispatch:</strong> Orders ship within 24-48 hours after payment confirmation</li>
            <li><strong>Weekend Orders:</strong> Orders placed on Saturday/Sunday ship on Monday</li>
        </ul>

        <h3>Delivery Methods</h3>
        <p>We partner with trusted courier services to ensure safe delivery:</p>
        <ul>
            <li><strong>Metro Manila:</strong> Standard courier service or same-day delivery options</li>
            <li><strong>Provincial Areas:</strong> Reliable nationwide courier partners</li>
            <li><strong>Tracking Available:</strong> Track your order from dispatch to delivery</li>
            <li><strong>Signature Required:</strong> For orders above ₱5,000</li>
        </ul>

        <h3>Order Tracking</h3>
        <p>Stay updated on your delivery status:</p>
        <ul>
            <li>Tracking number sent via email and SMS once order ships</li>
            <li>Track your order through "My Orders" section on our website</li>
            <li>Real-time updates on order status and location</li>
            <li>Email notifications for key delivery milestones</li>
            <li>Contact customer service for tracking assistance</li>
        </ul>

        <div class="highlight-box">
            <i class="fas fa-mobile-alt"></i>
            <strong>Stay Updated:</strong> You will receive SMS and email notifications at every stage of your delivery, from order confirmation to successful delivery.
        </div>

        <h3>Delivery Attempts</h3>
        <p>What happens during delivery:</p>
        <ul>
            <li><strong>First Attempt:</strong> Courier calls and attempts delivery to your address</li>
            <li><strong>No Answer:</strong> Notification card left with contact information</li>
            <li><strong>Second Attempt:</strong> Another delivery attempt within 24 hours</li>
            <li><strong>Failed Delivery:</strong> Package held at depot for pickup (valid ID required)</li>
            <li><strong>Return to Sender:</strong> After 3 failed attempts, item returns to us</li>
        </ul>

        <h3>Address Requirements</h3>
        <p>To ensure successful delivery, please provide:</p>
        <ul>
            <li>Complete street address including house/building number</li>
            <li>Barangay, city, and province information</li>
            <li>Accurate postal code</li>
            <li>Working mobile number for courier contact</li>
            <li>Landmark or special delivery instructions if needed</li>
            <li>Alternative contact person if you won't be available</li>
        </ul>

        <div class="highlight-box" style="background: #f8d7da; border-left-color: #dc3545;">
            <i class="fas fa-exclamation-triangle" style="color: #dc3545;"></i>
            <strong>Important:</strong> Incorrect or incomplete addresses may result in delivery delays or failed deliveries. Please double-check your shipping information before confirming your order.
        </div>

        <h3>Shipping Delays</h3>
        <p>Potential causes for delivery delays:</p>
        <ul>
            <li>Extreme weather conditions (typhoons, floods)</li>
            <li>Natural disasters or force majeure events</li>
            <li>Public holidays and non-working days</li>
            <li>Remote or hard-to-access locations</li>
            <li>Incorrect or incomplete delivery address</li>
            <li>Recipient unavailable during delivery attempts</li>
            <li>Peak season high volume (holidays, sales events)</li>
        </ul>

        <p><strong>Note:</strong> We will notify you immediately if any delays are expected. During peak seasons, please allow additional 1-2 days for delivery.</p>

        <h3>Damaged or Lost Packages</h3>
        <p>We take full responsibility for items damaged or lost in transit:</p>
        <ul>
            <li><strong>Damaged Package:</strong> Report within 48 hours of delivery with photos</li>
            <li><strong>Lost Package:</strong> Contact us if package not received within expected timeframe</li>
            <li><strong>Investigation Period:</strong> 5-7 business days for courier investigation</li>
            <li><strong>Resolution:</strong> Full refund or replacement at no additional cost</li>
            <li><strong>Claims Process:</strong> We handle all courier claims on your behalf</li>
        </ul>

        <h3>Inspection Upon Delivery</h3>
        <p>For your protection, please follow these steps:</p>
        <ul>
            <li>Inspect the package for external damage before accepting</li>
            <li>Check that all items are included and in good condition</li>
            <li>For Cash on Delivery: Verify contents before payment</li>
            <li>Sign delivery receipt only after inspection</li>
            <li>Report any issues to courier and contact us immediately</li>
        </ul>

        <div class="highlight-box">
            <i class="fas fa-camera"></i>
            <strong>Documentation:</strong> Take photos of damaged packages (inside and outside) before opening. This helps expedite the claims process.
        </div>

        <h3>Special Delivery Instructions</h3>
        <p>We accommodate special requests when possible:</p>
        <ul>
            <li><strong>Leave at Door:</strong> Not recommended for high-value items</li>
            <li><strong>Office Delivery:</strong> Provide office hours and contact person</li>
            <li><strong>Hold at Courier:</strong> Request depot pickup if preferred</li>
            <li><strong>Specific Time:</strong> Morning or afternoon delivery slots (Metro Manila only)</li>
            <li><strong>Gift Wrapping:</strong> Available upon request (additional fee may apply)</li>
        </ul>

        <h3>Cannot Ship To</h3>
        <p>We are unable to deliver to the following:</p>
        <ul>
            <li>PO Box addresses (physical address required)</li>
            <li>International addresses (outside Philippines)</li>
            <li>Military bases without proper clearance</li>
            <li>Areas declared under extreme calamity status</li>
        </ul>

        <h3>Multiple Item Orders</h3>
        <p>For orders with multiple products:</p>
        <ul>
            <li>All items typically ship together in one package</li>
            <li>Split shipments may occur if items are from different warehouses</li>
            <li>You will be notified if partial shipment is necessary</li>
            <li>No additional shipping fee for split shipments</li>
            <li>Each package receives its own tracking number</li>
        </ul>

        <h3>Change of Delivery Address</h3>
        <p>Need to change your shipping address?</p>
        <ul>
            <li>Contact us within 2 hours of order placement</li>
            <li>Address changes after dispatch may not be possible</li>
            <li>Additional fees may apply for address changes during transit</li>
            <li>Provide complete new address details immediately</li>
        </ul>

        <div class="highlight-box" style="background: #d1ecf1; border-left-color: #0c5460;">
            <i class="fas fa-clock" style="color: #0c5460;"></i>
            <strong>Quick Action:</strong> The faster you notify us about address changes, the better chance we have of updating your delivery details before dispatch.
        </div>

        <h3>Customer Responsibilities</h3>
        <p>To ensure smooth delivery, customers must:</p>
        <ul>
            <li>Provide accurate and complete shipping information</li>
            <li>Be available during specified delivery hours</li>
            <li>Respond to courier contact attempts promptly</li>
            <li>Inspect packages upon delivery</li>
            <li>Report any issues within specified timeframes</li>
        </ul>

        <h3>Contact Us for Shipping Inquiries</h3>
        <p>Need help with your delivery? Reach out to us:</p>
        <ul>
            <li><i class="fas fa-envelope me-2"></i><strong>Email:</strong> chavezleeann@gmail.com</li>
            <li><i class="fas fa-phone me-2"></i><strong>Phone:</strong> +63 926 765 8075</li>
            <li><i class="fas fa-clock me-2"></i><strong>Business Hours:</strong> Monday - Saturday, 9:00 AM - 6:00 PM</li>
            <li><i class="fas fa-map-marker-alt me-2"></i><strong>Address:</strong> 1127, Quezon City, Philippines</li>
        </ul>

        <p class="mt-4"><strong>Response Time:</strong> We respond to all shipping inquiries within 24 hours during business days.</p>
    </div>

    <div class="text-center">
        <a href="../user/index.php" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Shop
        </a>
    </div>
</div>

<footer class="footer-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="footer-title">CONTACT US</h5>
                <ul class="footer-links">
                    <li><i class="fas fa-map-marker-alt me-2"></i>University of Makati, Makati City, Philippines</li>
                    <li><i class="fas fa-phone me-2"></i>Call us: <a href="tel:+639123456789">+63 912 345 6789</a></li>
                    <li><i class="fas fa-envelope me-2"></i><a href="mailto:crcedits@gmail.com">crcedits@gmail.com</a></li>
                </ul>
                <div class="social-icons mt-3">
                    <a href="https://www.facebook.com/profile.php?id=100063584713268" target="_blank"><i class="fab fa-facebook-f"></i></a>
                    <a href="https://www.tiktok.com/@lian2.o?_t=8renerF9S8r&_r=1" target="_blank"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="footer-title">TOP BRANDS</h5>
                <ul class="footer-links">
                    <?php foreach ($brands as $brand): 
                        $brand_slug = strtolower(str_replace(' ', '', $brand));
                    ?>
                    <li><a href="../user/index.php#brand-<?php echo $brand_slug; ?>"><?php echo htmlspecialchars($brand); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="footer-title">SHOP</h5>
                <ul class="footer-links">
                    <li><a href="../user/index.php#new-arrivals">New Arrivals</a></li>
                    <li><a href="../user/index.php#best-seller">Best Seller</a></li>
                    <li><a href="../user/index.php#basketball-shoes">Basketball Shoes</a></li>
                    <li><a href="../user/index.php#running-shoes-men">Running Shoes Men</a></li>
                    <li><a href="../user/index.php#lifestyle-men">Lifestyle Men</a></li>
                    <li><a href="../user/index.php#running-shoes-women">Running Shoes Women</a></li>
                    <li><a href="../user/index.php#lifestyle-women">Lifestyle Women</a></li>
                    <li><a href="../user/index.php#brands">All Brands</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="footer-title">LEGAL</h5>
                <ul class="footer-links">
                    <li><a href="privacy_policy.php">Privacy Policy</a></li>
                    <li><a href="refund_policy.php">Refund Policy</a></li>
                    <li><a href="shipping_policy.php">Shipping Policy</a></li>
                    <li><a href="terms_of_service.php">Terms of Service</a></li>
                    <li><a href="payment_options.php">Payment Options</a></li>
                </ul>
            </div>
            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="footer-title">EXPLORE</h5>
                <ul class="footer-links">
                    <li><a href="../user/about.php">About Us</a></li>
                    <li><a href="../user/index.php">Shop</a></li>
                    <li><a href="../user/my_orders.php">My Orders</a></li>
                    <li><a href="../user/profile.php">My Profile</a></li>
                    <li><a href="../user/cart.php">Shopping Cart</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; 2025 LEE Sneakers. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="mb-0">Made with <i class="fas fa-heart text-danger"></i> by LEE Sneakers Team</p>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

    // Toggle Profile Dropdown
    function toggleProfile() {
        if (!isLoggedIn) {
            window.location.href = '../user/login.php';
            return;
        }
        const dropdown = document.getElementById('profileDropdown');
        if (dropdown) {
            dropdown.classList.toggle('show');
        }
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

    // Update Cart Badge
    function updateCartBadge() {
        if (!isLoggedIn) return;
        fetch('../user/get_cart_count.php')
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