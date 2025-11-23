<?php
require_once '../config/database.php';

// Define $is_logged_in here to avoid the "Undefined variable" warning
$is_logged_in = isLoggedIn(); 

// Redirect admins to admin panel
if (isAdmin()) {
    redirect('../admin/index.php');
}

// Since the user must be logged in to reach this point:
if ($is_logged_in) {
    $user_id = $_SESSION['user_id'];
}

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
    <title>About Us - LEE Sneakers</title>
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
        
        /* --- ABOUT PAGE SPECIFIC CSS --- */
        .about-hero {
            background: linear-gradient(135deg, #000435 0%, #001a5e 100%);
            color: white;
            padding: 150px 0 100px;
            text-align: center;
        }
        
        .about-hero h1 {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 20px;
        }
        
        .about-hero p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto;
        }
        
        .about-content {
            padding: 80px 0;
        }
        
        .about-card {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 50px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            line-height: 2;
        }
        
        .about-card h2 {
            color: #000435;
            margin-bottom: 30px;
            font-weight: bold;
            font-size: 2.5rem;
        }
        
        .about-card p {
            color: #333;
            font-size: 1.15rem;
            text-align: justify;
            margin-bottom: 20px;
        }
        
        .highlight {
            color: #FEC700;
            font-weight: 600;
        }
        
        .about-image-container {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 15px;
        }
        
        .about-image {
            width: 100%;
            max-width: 250px; 
            height: auto;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            object-fit: cover;
        }

        .about-card .row {
            align-items: center;
        }

        @media (max-width: 991.98px) {
            .about-image-col {
                order: -1;
                margin-bottom: 30px;
            }
            .about-image-container {
                padding: 0;
            }
            .about-card {
                padding: 30px;
            }
        }
        
        .contact-section {
            padding: 80px 0;
            background: #f8f9fa;
        }
        
        .contact-card {
            background: white;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .contact-icon {
            font-size: 2.5rem;
            color: #FEC700;
            margin-right: 25px;
            width: 60px;
            text-align: center;
        }
        
        .contact-item h5 {
            margin-bottom: 5px;
            font-weight: bold;
            color: #000435;
        }
        
        .contact-item p {
            margin: 0;
            color: #666;
            font-size: 1.1rem;
        }
        
        .contact-item a {
            color: #000435;
            text-decoration: none;
        }
        
        .contact-item a:hover {
            color: #FEC700;
            text-decoration: underline;
        }
        
        .social-links {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .social-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            background: #000435;
            color: #FEC700;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0, 4, 53, 0.3);
            border: 2px solid #FEC700;
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(254, 199, 0, 0.5);
            background: #FEC700;
            color: #000435;
        }
        
        .social-btn i {
            font-size: 1.3rem;
        }
        
        .modal-content {
            border-radius: 15px;
        }
        .modal-header {
            background-color: #000435;
            color: white;
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }
        .modal-body img {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
            width: 100%; 
            height: auto;
        }
        .carousel-item {
            padding: 15px;
        }

        /* Chatbot Styles */
        .chatbot-widget {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
            display: none;
        }
        .chat-window {
            position: absolute;
            bottom: 90px;
            right: 0;
            width: 380px;
            height: 600px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            overflow: hidden;
            animation: slideUp 0.3s ease;
        }
        .chat-window.active {
            display: flex;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
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
        }
        .chat-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
            flex: 1;
            text-align: center;
        }
        .chat-back-btn, .chat-close-btn {
            background: none;
            border: none;
            color: #000435;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .chat-back-btn { font-size: 18px; padding: 4px 8px; }
        .chat-close-btn { font-size: 20px; }
        .chat-back-btn:hover, .chat-close-btn:hover { transform: scale(1.2); }
        .chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 16px;
            background: #f9f9f9;
        }
        .message {
            margin-bottom: 12px;
            display: flex;
            animation: fadeIn 0.3s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .message.bot { justify-content: flex-start; }
        .message.user { justify-content: flex-end; }
        .message-content {
            max-width: 70%;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 14px;
            line-height: 1.4;
        }
        .bot .message-content { background: #e8e8e8; color: #333; }
        .user .message-content { background: #FEC700; color: #000435; font-weight: 500; }
        .quick-replies {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 12px;
        }
        .quick-reply-btn {
            background: white;
            border: 2px solid #FEC700;
            color: #FEC700;
            padding: 10px 14px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
            text-align: left;
        }
        .quick-reply-btn:hover { background: #FEC700; color: #000435; }
        .chat-input-section {
            padding: 12px;
            background: white;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 8px;
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
        .chat-input:focus { border-color: #FEC700; }
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
        .chat-send-btn:hover { background: #f0c800; transform: translateY(-2px); }
        .chat-greeting {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 20px;
            text-align: center;
        }
        .greeting-icon { font-size: 48px; margin-bottom: 16px; }
        .greeting-title { font-size: 16px; font-weight: 700; color: #000435; margin-bottom: 8px; }
        .greeting-text { font-size: 13px; color: #666; margin-bottom: 20px; line-height: 1.4; }
        @media (max-width: 480px) {
            .chat-window { width: 100vw; height: 100vh; bottom: 0; right: 0; border-radius: 0; max-width: 100%; }
            .chat-bubble-btn { width: 50px; height: 50px; font-size: 20px; }
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

    <section class="about-hero">
        <div class="container">
            <h1>About LEE Sneakers</h1>
            <p>A trusted source for stylish and affordable shoes since 2022</p>
        </div>
    </section>

    <section class="about-content">
        <div class="container">
            <div class="about-card">
                <div class="row">
                    <div class="col-lg-4 col-md-4 about-image-col">
                        <div class="about-image-container">
                            <img src="../proof of transactions/pic nya.jpg" class="about-image" alt="Lee Ann Chavez, Founder of LEE Sneakers">
                        </div>
                    </div>
                    
                    <div class="col-lg-8 col-md-8">
                        <h2><i class="fas fa-heart me-3"></i>ABOUT US</h2>
                        <p>
                            Welcome to <span class="highlight">Lee Sneakers</span>, a trusted source for stylish and affordable shoes since 2022. 
                            Founded by <span class="highlight">Lian Chavez</span>, a young entrepreneur with a big dream, our business began with 
                            a simple passion for sneakers and a desire to share that love with others.
                        </p>
                        <p>
                            At just <span class="highlight">15 years old</span>, Lee Ann started her journey in shoe reselling after being inspired by a 
                            close friend who taught her how to find and sell quality sneakers. What began as small, personal sales soon grew as more 
                            customers discovered her great finds and reliable service. As demand increased, Lee Ann decided to create an online page 
                            so more people could explore her products — and that's how <span class="highlight">Lee Sneakers</span> officially took shape. 
                            The name itself comes from her own name, representing her personal dedication and heart in every pair she sells.
                        </p>
                        <p>
                            Today, <span class="highlight">Lee Sneakers</span> continues to grow, offering a variety of trendy, authentic, and 
                            budget-friendly footwear for sneaker lovers everywhere. We take pride in our commitment to quality, customer satisfaction, 
                            and passion for style.
                        </p>
                        <p>
                            At <span class="highlight">Lee Sneakers</span>, we believe that <span class="highlight">every step counts</span> — 
                            and we're here to help you take yours in confidence and comfort.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="contact-section">
        <div class="container">
            <div class="text-center mb-5">
                <h2 style="color: #000435; font-weight: bold; font-size: 2.5rem;">Contact Information</h2>
            </div>
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="contact-card">
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <h5>Email</h5>
                                <p><a href="mailto:crcedits@gmail.com">chavezleeann@gmail.com</a></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <h5>Phone</h5>
                                <p><a href="tel:+639123456789">+63 926 765 8075</a></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-icon">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h5>Location</h5>
                                <p>1127, Quezon City, Philippines</p>
                            </div>
                        </div>
                        <hr style="margin: 30px 0;">
                        <div class="text-center">
                            <h5 style="margin-bottom: 20px; color: #000435; font-weight: bold;">Connect With Us</h5>
                            <div class="social-links">
                                <a href="https://www.facebook.com/profile.php?id=100063584713268" target="_blank" class="social-btn" title="Lian Chavez - Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                    <span>Lian Chavez</span>
                                </a>
                                <a href="https://www.facebook.com/profile.php?id=61566028473735" target="_blank" class="social-btn" title="Lee Sneakers - Facebook Page">
                                    <i class="fab fa-facebook-f"></i>
                                    <span>Lee Sneakers Page</span>
                                </a>
                                <a href="https://www.tiktok.com/@lian2.o?_t=8renerF9S8r&_r=1" target="_blank" class="social-btn" title="Lee Sneakers - TikTok">
                                    <i class="fab fa-tiktok"></i>
                                    <span>Lee Sneakers</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="proofOfPaymentModal" tabindex="-1" aria-labelledby="proofOfPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="proofOfPaymentModalLabel">Proof of Transactions (Trusted Customers)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <div id="paymentCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            <button type="button" data-bs-target="#paymentCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                            <button type="button" data-bs-target="#paymentCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                            <button type="button" data-bs-target="#paymentCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                            <button type="button" data-bs-target="#paymentCarousel" data-bs-slide-to="3" aria-label="Slide 4"></button>
                            <button type="button" data-bs-target="#paymentCarousel" data-bs-slide-to="4" aria-label="Slide 5"></button>
                            <button type="button" data-bs-target="#paymentCarousel" data-bs-slide-to="5" aria-label="Slide 6"></button>
                            <button type="button" data-bs-target="#paymentCarousel" data-bs-slide-to="6" aria-label="Slide 7"></button>
                            <button type="button" data-bs-target="#paymentCarousel" data-bs-slide-to="7" aria-label="Slide 8"></button>
                            <button type="button" data-bs-target="#paymentCarousel" data-bs-slide-to="8" aria-label="Slide 9"></button>
                        </div>
                        
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="../proof of transactions/5e6dfc5b8c0f819a8e1ca77d2bda4401.jpeg" class="d-block w-100" alt="Proof of Transaction 1">
                            </div>
                            <div class="carousel-item">
                                <img src="../proof of transactions/9fdcc8b96745be9145d96d5c7824d71f.jpeg" class="d-block w-100" alt="Proof of Transaction 2">
                            </div>
                            <div class="carousel-item">
                                <img src="../proof of transactions/300fb2a5a8dfd339e22d307853e215b7.jpeg" class="d-block w-100" alt="Proof of Transaction 3">
                            </div>
                            <div class="carousel-item">
                                <img src="../proof of transactions/74894dc90cb31996f17dddae1c15c131.jpeg" class="d-block w-100" alt="Proof of Transaction 4">
                            </div>
                            <div class="carousel-item">
                                <img src="../proof of transactions/a7cd772e688d6bbfd6e820703b054a51.jpeg" class="d-block w-100" alt="Proof of Transaction 5">
                            </div>
                            
                            <div class="carousel-item">
                                <img src="../proof of transactions/att.nm57zMxwePTvwKwBmTkuILDWrhfCxp8L2Z7W8XEbWmM.jpg" class="d-block w-100" alt="Proof of Transaction 6">
                            </div>
                            <div class="carousel-item">
                                <img src="../proof of transactions/att.s5yNglIFw2Cc1JCxPQ5cUCeejH7iGAJ0hN-OSsdO7jg.jpg" class="d-block w-100" alt="Proof of Transaction 7">
                            </div>
                            <div class="carousel-item">
                                <img src="../proof of transactions/c15f5d777bfb2620a6134d266d0e1033.jpeg" class="d-block w-100" alt="Proof of Transaction 8">
                            </div>
                            <div class="carousel-item">
                                <img src="../proof of transactions/att.nm57zMxwePTvwKwBmTkuILDWrhfCxp8L2Z7W8XEbWmM.jpg" class="d-block w-100" alt="Proof of Transaction 9">
                            </div>
                        </div>

                        <button class="carousel-control-prev" type="button" data-bs-target="#paymentCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#paymentCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="chatbot-widget">
        <button class="chat-bubble-btn" id="chatBubbleBtn" onclick="toggleChat()">
            <i class="fas fa-comments"></i>
        </button>

        <div class="chat-window" id="chatWindow">
            <div class="chat-header">
                <h3 id="chatHeaderTitle">LEE Sneakers Support</h3>
                <button class="chat-back-btn" id="chatBackBtn" onclick="goBackToMenu()" style="display: none;" title="Back">
                    <i class="fas fa-arrow-left"></i>
                </button>
                <button class="chat-close-btn" onclick="toggleChat()">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="chat-messages" id="chatMessages">
                <div class="message bot">
                    <div class="message-content">
                        <div class="greeting-icon">👋</div>
                        <div class="greeting-title">Chat with us</div>
                        <div class="greeting-text">Hi, message us with any questions. We're happy to help!</div>
                    </div>
                </div>
            </div>

            <div id="quickRepliesContainer" style="padding: 0 16px; margin-bottom: 12px;">
                <div class="quick-replies">
                    <button class="quick-reply-btn" onclick="handleQuickReply('track')">
                        <i class="fas fa-box me-2"></i>Track my order
                    </button>
                    <button class="quick-reply-btn" onclick="handleQuickReply('contact')">
                        <i class="fas fa-phone me-2"></i>What is your contact info?
                    </button>
                    <button class="quick-reply-btn" onclick="handleQuickReply('shipping')">
                        <i class="fas fa-truck me-2"></i>What are your shipping details?
                    </button>
                    <button class="quick-reply-btn" onclick="handleQuickReply('delivery')">
                        <i class="fas fa-calendar-check me-2"></i>When can I expect my order?
                    </button>
                    <button class="quick-reply-btn" onclick="handleQuickReply('refund')">
                        <i class="fas fa-undo me-2"></i>I want to refund my order
                    </button>
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

    <?php 
    // $page_level ensures correct linking (e.g., ./ for user folder, ../user/ for other folders)
    $page_level = './'; // We are in the 'user' directory
    require_once '../components/footer.php'; 
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Use PHP variable to determine login status for JS logic
        var isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

        // Toggle Profile Dropdown
        function toggleProfile() {
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

        // Toggle Chat Window
        function toggleChat() {
            const chatWindow = document.getElementById('chatWindow');
            const chatBubbleBtn = document.getElementById('chatBubbleBtn');
            chatWindow.classList.toggle('active');
            chatBubbleBtn.classList.toggle('active');
        }

        // Go Back to Menu (Modified to preserve chat history)
        function goBackToMenu() {
            const chatMessages = document.getElementById('chatMessages');
            const chatBackBtn = document.getElementById('chatBackBtn');
            const chatHeaderTitle = document.getElementById('chatHeaderTitle');
            const quickRepliesContainer = document.getElementById('quickRepliesContainer');

            // 1. Show the quick reply container (which was hidden after first interaction)
            quickRepliesContainer.style.display = 'block';

            // 2. Hide the back button and reset header
            chatBackBtn.style.display = 'none';
            chatHeaderTitle.textContent = 'LEE Sneakers Support';

            // 3. Scroll to top (to show the initial greeting)
            chatMessages.scrollTop = 0;
        }

        // Handle Quick Replies (Modified to hide quick replies and display back button)
        function handleQuickReply(action) {
            const responses = {
                track: {
                    title: 'Track my order',
                    message: 'To see your order status, please provide your order details.',
                    followUp: 'Please enter your order information'
                },
                contact: {
                    title: 'What is your contact info?',
                    message: 'Hi! You can reach out to our branches here:\n\n📍 Bonifacio High Street - 09688870943\n📍 Glorietta 3 - 09988466582\n📍 SPATIO, Opus - 09190784793\n📍 Online - 09988465719\n\nYou may also send us an email at chavezleeann@gmail.com should you have any concerns.',
                    followUp: ''
                },
                shipping: {
                    title: 'What are your shipping details?',
                    message: '1. We ship nationwide! Shipping fees may vary depending on your indicated delivery address.\n\n2. Our official courier partner is JRS Express.\n\n3. You may expect your order within 3-5 days for Metro Manila addresses and 5-7 days for provincial addresses (Subject to delays due to inclement weather, holidays, and days with a high amount of orders).',
                    followUp: ''
                },
                delivery: {
                    title: 'When can I expect my order?',
                    message: 'You may expect your order to be delivered within the next 3-5 working days for Metro Manila and 5-8 working days for provincial areas. Please note that holidays and intermittent weather may subject your order to delays.',
                    followUp: ''
                },
                refund: {
                    title: 'I want to refund my order',
                    message: 'If you have a concern with your item and would like to request a return and refund, please send us an email at chavezleeann@gmail.com so our team can review accordingly. We will get back to you soon!\n\n*Please note that sending us an email does not guarantee a return or refund. All requests will be subject to approval.',
                    followUp: ''
                }
            };

            const response = responses[action];

            // Add user message
            addMessage(response.title, 'user');

            // 1. Hide quick replies container
            const container = document.getElementById('quickRepliesContainer');
            container.style.display = 'none';

            // 2. Show back button
            const chatBackBtn = document.getElementById('chatBackBtn');
            const chatHeaderTitle = document.getElementById('chatHeaderTitle');
            chatBackBtn.style.display = 'block';
            chatHeaderTitle.textContent = 'LEE Sneakers Support';

            // 3. Add bot response
            setTimeout(() => {
                addMessage(response.message, 'bot');
                if (response.followUp) {
                    setTimeout(() => {
                        addMessage(response.followUp, 'bot');
                    }, 500);
                }
            }, 300);

            // Clear input
            document.getElementById('chatInput').value = '';
        }

        // Add Message to Chat
        function addMessage(text, sender) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;

            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            // Check for line breaks and replace with <br> for proper rendering in HTML
            contentDiv.innerHTML = text.replace(/\n/g, '<br>');

            messageDiv.appendChild(contentDiv);
            chatMessages.appendChild(messageDiv);

            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Send User Message
        function sendMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();

            if (!message) return;

            // Add user message
            addMessage(message, 'user');
            input.value = '';

            // Auto-reply after delay
            setTimeout(() => {
                const autoReply = getAutoReply(message);
                addMessage(autoReply, 'bot');
            }, 500);
        }

        // Get Auto-Reply Based on Keywords
        function getAutoReply(message) {
            const msg = message.toLowerCase();

            if (msg.includes('order') || msg.includes('track')) {
                return 'To view your order status, please visit your profile or provide your order ID. You can also contact us at chavezleeann@gmail.com for assistance.';
            } else if (msg.includes('contact') || msg.includes('phone') || msg.includes('email')) {
                return 'You can reach us at:\n📞 09988465719\n📧 chavezleeann@gmail.com\n\nWe\'re happy to help!';
            } else if (msg.includes('shipping') || msg.includes('delivery')) {
                return 'We deliver nationwide within 3-5 days for Metro Manila and 5-7 days for provincial areas via JRS Express. Fees vary by location.';
            } else if (msg.includes('refund') || msg.includes('return')) {
                return 'For refund requests, please email chavezleeann@gmail.com with details. Our team will review and get back to you soon!';
            } else if (msg.includes('hello') || msg.includes('hi') || msg.includes('hey')) {
                return 'Hello! Welcome to LEE Sneakers! How can we assist you today?';
            } else if (msg.includes('thank') || msg.includes('thanks')) {
                return 'You\'re welcome! Is there anything else we can help you with?';
            } else {
                return 'Thanks for your message! For more details, please select one of our quick reply options or contact us at chavezleeann@gmail.com.';
            }
        }

        // Handle Enter Key
        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                sendMessage();
            }
        }
    </script>
</body>
</html>