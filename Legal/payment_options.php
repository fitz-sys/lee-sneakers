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
    <title>Payment Options - LEE Sneakers</title>
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

        /* --- PAYMENT OPTIONS SPECIFIC STYLES --- */
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

        .payment-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .payment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0,0,0,0.15);
        }

        .payment-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid #FEC700;
        }

        .payment-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #FEC700 0%, #ffd700 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #000435;
        }

        .payment-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: #000435;
            margin: 0;
        }

        .payment-details h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #064734;
            margin: 25px 0 15px;
        }

        .payment-details ul {
            list-style: none;
            padding: 0;
        }

        .payment-details ul li {
            padding: 12px 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: start;
            gap: 15px;
        }

        .payment-details ul li:last-child {
            border-bottom: none;
        }

        .payment-details ul li i {
            color: #FEC700;
            font-size: 1.2rem;
            margin-top: 3px;
            flex-shrink: 0;
        }

        .gcash-qr {
            background: #f8f9fa;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            margin: 25px 0;
            border: 3px dashed #FEC700;
        }

        .gcash-qr img {
            max-width: 300px;
            width: 100%;
            height: auto;
            border: 5px solid white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }

        .gcash-qr p {
            margin-top: 15px;
            font-weight: 600;
            color: #064734;
            font-size: 1.1rem;
        }

        .alert-custom {
            background: #fff3cd;
            border-left: 5px solid #ffc107;
            padding: 20px;
            border-radius: 8px;
            margin: 25px 0;
        }

        .alert-custom i {
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
            .payment-card {
                padding: 25px;
            }
            .payment-header {
                flex-direction: column;
                text-align: center;
            }
            .payment-icon {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }
            .gcash-qr img {
                max-width: 250px;
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
        <h1><i class="fas fa-credit-card me-3"></i>Payment Options</h1>
        <p>Safe, Secure, and Convenient Payment Methods</p>
    </div>
</div>

<div class="content-section">
    
    <div class="payment-card">
        <div class="payment-header">
            <div class="payment-icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
            <div>
                <h2>GCash Payment</h2>
                <p class="mb-0 text-muted">Fast and Secure Digital Payment</p>
            </div>
        </div>

        <div class="payment-details">
            <h3><i class="fas fa-info-circle me-2"></i>How to Pay via GCash</h3>
            <ul>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Select GCash as your payment method during checkout</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Scan the QR code below using your GCash app</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Enter the exact order amount</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Take a screenshot of your payment confirmation</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Upload the screenshot during checkout</span>
                </li>
            </ul>

            <div class="gcash-qr">
                <img src="../uploads/qr/code.jpg" alt="GCash QR Code">
                <p><i class="fas fa-qrcode me-2"></i>Scan this QR Code to Pay</p>
            </div>

            <div class="alert-custom">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Important:</strong> GCash payment is available for <strong>Nationwide Delivery</strong> within the Philippines only. International orders are not accepted.
            </div>

            <h3><i class="fas fa-shield-alt me-2"></i>GCash Payment Benefits</h3>
            <ul>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>Instant Processing:</strong> Your order is confirmed immediately after payment verification</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>Secure Transaction:</strong> Protected by GCash's advanced security system</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>No Cash Needed:</strong> Pay directly from your GCash wallet</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>24/7 Available:</strong> Make payments anytime, anywhere</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="payment-card">
        <div class="payment-header">
            <div class="payment-icon">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div>
                <h2>Cash on Delivery (COD)</h2>
                <p class="mb-0 text-muted">Pay When You Receive Your Order</p>
            </div>
        </div>

        <div class="payment-details">
            <h3><i class="fas fa-info-circle me-2"></i>How COD Works</h3>
            <ul>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Select "Cash on Delivery" during checkout</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Complete your order details</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Wait for your order to be delivered</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Inspect the product upon delivery</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span>Pay the exact amount in cash to the delivery rider</span>
                </li>
            </ul>

            <div class="alert-custom">
                <i class="fas fa-map-marker-alt"></i>
                <strong>Coverage Area:</strong> Cash on Delivery is only available <strong>within Metro Manila</strong>. Customers outside Metro Manila must use GCash payment.
            </div>

            <h3><i class="fas fa-thumbs-up me-2"></i>COD Advantages</h3>
            <ul>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>Inspect Before Payment:</strong> Check your order before paying</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>No Online Transaction Needed:</strong> Perfect for those without digital wallets</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>Free Shipping:</strong> Enjoy free delivery within Metro Manila</span>
                </li>
                <li>
                    <i class="fas fa-check-circle"></i>
                    <span><strong>Simple Process:</strong> No need for screenshots or online payment</span>
                </li>
            </ul>

            <div class="alert-custom" style="background: #d1ecf1; border-left-color: #0c5460;">
                <i class="fas fa-lightbulb" style="color: #0c5460;"></i>
                <strong>Tip:</strong> Please prepare the exact amount to make the transaction faster and easier for both you and the delivery rider.
            </div>
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
                    <li><a href="../Legal/privacy_policy.php">Privacy Policy</a></li>
                    <li><a href="../Legal/refund_policy.php">Refund Policy</a></li>
                    <li><a href="../Legal/shipping_policy.php">Shipping Policy</a></li>
                    <li><a href="../Legal/terms_of_service.php">Terms of Service</a></li>
                    <li><a href="../Legal/payment_options.php">Payment Options</a></li>
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