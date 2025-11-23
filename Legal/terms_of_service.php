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
    <title>Terms of Service - LEE Sneakers</title>
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

        /* --- TERMS OF SERVICE SPECIFIC STYLES --- */
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
        <h1><i class="fas fa-file-contract me-3"></i>Terms of Service</h1>
        <p>Please Read These Terms Carefully</p>
        <p class="small mt-2">Last Updated: November 2025</p>
    </div>
</div>

<div class="content-section">
    <div class="policy-card">
        <h2>Agreement to Terms</h2>
        <p>Welcome to LEE Sneakers. These Terms of Service ("Terms") govern your access to and use of our website, products, and services. By accessing or using LEE Sneakers, you agree to be bound by these Terms and all applicable laws and regulations.</p>
        
        <div class="highlight-box">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Important:</strong> If you do not agree with any part of these Terms, you must not use our website or services.
        </div>

        <h3>Eligibility</h3>
        <p>To use our services, you must meet the following requirements:</p>
        <ul>
            <li>Be at least 18 years of age</li>
            <li>Have the legal capacity to enter into binding contracts</li>
            <li>Not be prohibited from using our services under Philippine law</li>
            <li>Provide accurate and complete registration information</li>
            <li>Maintain the security of your account credentials</li>
        </ul>

        <h3>Account Registration</h3>
        <p>When creating an account with LEE Sneakers:</p>
        <ul>
            <li>You must provide truthful and accurate information</li>
            <li>You are responsible for maintaining account confidentiality</li>
            <li>You must notify us immediately of any unauthorized access</li>
            <li>You are responsible for all activities under your account</li>
            <li>We reserve the right to suspend or terminate accounts that violate these Terms</li>
            <li>One person may not maintain multiple accounts</li>
        </ul>

        <h3>Product Information</h3>
        <p>Regarding our product listings:</p>
        <ul>
            <li>We strive to provide accurate product descriptions and images</li>
            <li>Colors may vary slightly due to monitor settings</li>
            <li>Product availability is subject to change without notice</li>
            <li>Prices are displayed in Philippine Pesos (₱)</li>
            <li>We reserve the right to modify prices at any time</li>
            <li>Typographical errors will be corrected promptly</li>
        </ul>

        <div class="highlight-box" style="background: #d1ecf1; border-left-color: #0c5460;">
            <i class="fas fa-info-circle" style="color: #0c5460;"></i>
            <strong>Note:</strong> All products are 100% authentic. We do not sell counterfeit or replica items.
        </div>

        <h3>Ordering and Payment</h3>
        <p>When placing an order:</p>
        <ul>
            <li>All orders are subject to acceptance and product availability</li>
            <li>We reserve the right to refuse or cancel any order</li>
            <li>Payment must be completed before order processing</li>
            <li>Accepted payment methods: GCash and Cash on Delivery (Metro Manila only)</li>
            <li>Prices include applicable taxes unless otherwise stated</li>
            <li>Order confirmation does not guarantee product availability</li>
        </ul>

        <h3>Pricing and Promotions</h3>
        <p>Regarding our pricing policy:</p>
        <ul>
            <li>All prices are in Philippine Pesos (₱)</li>
            <li>We reserve the right to change prices without prior notice</li>
            <li>Promotional offers are valid for specified periods only</li>
            <li>Sale items may have limited availability</li>
            <li>Discount codes cannot be combined unless specified</li>
            <li>Pricing errors will be corrected, and affected customers will be notified</li>
        </ul>

        <h3>Shipping and Delivery</h3>
        <p>Our shipping terms:</p>
        <ul>
            <li>Delivery is available nationwide within the Philippines only</li>
            <li>Delivery times are estimates and not guaranteed</li>
            <li>We are not liable for delays caused by courier or force majeure</li>
            <li>Risk of loss transfers to you upon delivery</li>
            <li>Customers must provide accurate delivery addresses</li>
            <li>Failed deliveries due to incorrect addresses may incur additional fees</li>
        </ul>

        <h3>Returns and Refunds</h3>
        <p>Please refer to our Refund Policy for detailed information. Key points:</p>
        <ul>
            <li>Returns must be initiated within 7 days of delivery</li>
            <li>Products must be unused and in original condition</li>
            <li>Refunds are processed within 5-10 business days after approval</li>
            <li>Sale and clearance items may have different return policies</li>
            <li>Defective products are covered under our quality guarantee</li>
        </ul>

        <h3>Prohibited Uses</h3>
        <p>You agree not to use our services to:</p>
        <ul>
            <li>Violate any applicable laws or regulations</li>
            <li>Infringe on intellectual property rights</li>
            <li>Transmit malicious code or viruses</li>
            <li>Attempt unauthorized access to our systems</li>
            <li>Engage in fraudulent activities</li>
            <li>Resell products for commercial purposes without authorization</li>
            <li>Use automated systems (bots) to access the website</li>
            <li>Harass or harm other users or our staff</li>
        </ul>

        <div class="highlight-box" style="background: #f8d7da; border-left-color: #dc3545;">
            <i class="fas fa-ban" style="color: #dc3545;"></i>
            <strong>Violations:</strong> Any violation of these prohibited uses may result in immediate account termination and legal action.
        </div>

        <h3>Intellectual Property</h3>
        <p>All content on LEE Sneakers is protected:</p>
        <ul>
            <li>Website design, logo, and branding are owned by LEE Sneakers</li>
            <li>Product images, descriptions, and content are copyrighted</li>
            <li>Unauthorized use or reproduction is strictly prohibited</li>
            <li>Brand names and trademarks belong to their respective owners</li>
            <li>You may not use our content for commercial purposes</li>
        </ul>

        <h3>User Content</h3>
        <p>When you submit content (reviews, photos, comments):</p>
        <ul>
            <li>You grant us a license to use, reproduce, and display your content</li>
            <li>You represent that you own or have rights to the content</li>
            <li>We reserve the right to remove inappropriate content</li>
            <li>You are responsible for the accuracy of your submissions</li>
            <li>Content must not violate laws or infringe on rights of others</li>
        </ul>

        <h3>Privacy and Data Protection</h3>
        <p>Your privacy is important to us:</p>
        <ul>
            <li>Our Privacy Policy governs the collection and use of your information</li>
            <li>We implement security measures to protect your data</li>
            <li>We do not sell your personal information to third parties</li>
            <li>You have rights to access, correct, and delete your data</li>
            <li>We comply with the Data Privacy Act of 2012 (Republic Act No. 10173)</li>
        </ul>

        <h3>Limitation of Liability</h3>
        <p>To the fullest extent permitted by law:</p>
        <ul>
            <li>LEE Sneakers is not liable for indirect or consequential damages</li>
            <li>Our total liability shall not exceed the purchase price of the product</li>
            <li>We are not responsible for third-party websites or services</li>
            <li>We do not guarantee uninterrupted or error-free service</li>
            <li>You use our services at your own risk</li>
        </ul>

        <h3>Indemnification</h3>
        <p>You agree to indemnify and hold harmless LEE Sneakers from:</p>
        <ul>
            <li>Any claims arising from your violation of these Terms</li>
            <li>Your misuse of our products or services</li>
            <li>Infringement of any third-party rights</li>
            <li>Any content you submit to our platform</li>
        </ul>

        <h3>Dispute Resolution</h3>
        <p>In case of disputes:</p>
        <ul>
            <li>We encourage direct communication to resolve issues</li>
            <li>Disputes shall be governed by Philippine law</li>
            <li>Exclusive jurisdiction lies with courts in Quezon City, Philippines</li>
            <li>Both parties agree to attempt good-faith resolution before litigation</li>
        </ul>

        <h3>Force Majeure</h3>
        <p>We are not liable for failures caused by:</p>
        <ul>
            <li>Natural disasters (typhoons, earthquakes, floods)</li>
            <li>Government actions or regulations</li>
            <li>War, terrorism, or civil unrest</li>
            <li>Pandemics or public health emergencies</li>
            <li>Internet or telecommunications failures</li>
            <li>Other events beyond our reasonable control</li>
        </ul>

        <h3>Modifications to Terms</h3>
        <p>We reserve the right to modify these Terms:</p>
        <ul>
            <li>Changes will be posted on this page with updated date</li>
            <li>Continued use constitutes acceptance of modified Terms</li>
            <li>Significant changes will be communicated via email</li>
            <li>Review Terms periodically for updates</li>
        </ul>

        <div class="highlight-box">
            <i class="fas fa-clock"></i>
            <strong>Stay Informed:</strong> Check this page regularly for any updates to our Terms of Service.
        </div>

        <h3>Account Termination</h3>
        <p>We may suspend or terminate your account if:</p>
        <ul>
            <li>You violate these Terms of Service</li>
            <li>You engage in fraudulent activities</li>
            <li>Your account is inactive for an extended period</li>
            <li>Required by law or legal authority</li>
            <li>You request account deletion</li>
        </ul>

        <h3>Severability</h3>
        <p>If any provision of these Terms is found to be unenforceable:</p>
        <ul>
            <li>The invalid provision will be modified to the minimum extent necessary</li>
            <li>All other provisions remain in full force and effect</li>
            <li>The intent of the original provision will be preserved</li>
        </ul>

        <h3>Entire Agreement</h3>
        <p>These Terms, along with our Privacy Policy, Refund Policy, and Shipping Policy, constitute the entire agreement between you and LEE Sneakers regarding your use of our services.</p>

        <h3>Contact Information</h3>
        <p>For questions or concerns about these Terms of Service:</p>
        <ul>
            <li><i class="fas fa-envelope me-2"></i><strong>Email:</strong> chavezleeann@gmail.com</li>
            <li><i class="fas fa-phone me-2"></i><strong>Phone:</strong> +63 926 765 8075</li>
            <li><i class="fas fa-clock me-2"></i><strong>Business Hours:</strong> Monday - Saturday, 9:00 AM - 6:00 PM</li>
            <li><i class="fas fa-map-marker-alt me-2"></i><strong>Address:</strong> 1127, Quezon City, Philippines</li>
        </ul>

        <div class="highlight-box" style="background: #d1ecf1; border-left-color: #0c5460;">
            <i class="fas fa-handshake" style="color: #0c5460;"></i>
            <strong>Thank You:</strong> We appreciate your business and trust in LEE Sneakers. By using our services, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service.
        </div>
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