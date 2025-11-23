<?php
require_once '../config/database.php';

// Check if user is logged in
$is_logged_in = isLoggedIn();
$username = $is_logged_in ? $_SESSION['username'] : '';

// --- FETCH BRANDS FOR NAVBAR ---
// We keep this because the Navbar inside this file still needs the brands list
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
    <title>Privacy Policy - LEE Sneakers</title>
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
            background: #f8f9fa;
            font-family: 'Poppins', sans-serif;
            padding-top: 100px;
        }

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

        /* --- PRIVACY POLICY SPECIFIC STYLES --- */
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
          FOR MEN
        </a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="../user/index.php#basketball-shoes">BASKETBALL SHOES</a></li>
          <li><a class="dropdown-item" href="../user/index.php#running-shoes-men">RUNNING SHOES</a></li>
          <li><a class="dropdown-item" href="../user/index.php#lifestyle-men">LIFESTYLE</a></li>
        </ul>
      </li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" href="#" id="womenDropdown" role="button" data-bs-toggle="dropdown">
          FOR WOMEN
        </a>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="../user/index.php#running-shoes-women">RUNNING SHOES</a></li>
          <li><a class="dropdown-item" href="../user/index.php#lifestyle-women">LIFESTYLE</a></li>
        </ul>
      </li>
      <li class="nav-item">
        <a class="nav-link" href="../user/index.php#kids">FOR KIDS</a>
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
                            MEN
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
                            WOMEN
                        </button>
                    </h2>
                    <div id="womenCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion">
                        <div class="accordion-body p-0">
                            <a class="nav-link ps-4" href="../user/index.php#running-shoes-women" data-bs-dismiss="offcanvas">RUNNING SHOES</a>
                            <a class="nav-link ps-4" href="../user/index.php#lifestyle-women" data-bs-dismiss="offcanvas">LIFESTYLE</a>
                        </div>
                    </div>
                </div>
                
                <a class="nav-link" href="../user/index.php#kids" data-bs-dismiss="offcanvas">KIDS</a>
                
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
        <h1><i class="fas fa-shield-alt me-3"></i>Privacy Policy</h1>
        <p>Your Privacy is Important to Us</p>
        <p class="small mt-2">Last Updated: November 2025</p>
    </div>
</div>

<div class="content-section">
    <div class="policy-card">
        <h2>Introduction</h2>
        <p>Welcome to LEE Sneakers. We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services.</p>
        
        <div class="highlight-box">
            <i class="fas fa-info-circle"></i>
            <strong>Important:</strong> By using LEE Sneakers, you agree to the collection and use of information in accordance with this Privacy Policy.
        </div>

        <h3>Information We Collect</h3>
        <p>We collect several types of information to provide and improve our services:</p>
        <ul>
            <li><strong>Personal Information:</strong> Name, email address, phone number, and shipping address</li>
            <li><strong>Account Information:</strong> Username and password for your account</li>
            <li><strong>Payment Information:</strong> Payment method details and transaction records</li>
            <li><strong>Order Information:</strong> Products purchased, order history, and delivery preferences</li>
            <li><strong>Device Information:</strong> IP address, browser type, and operating system</li>
            <li><strong>Usage Data:</strong> Pages visited, time spent on site, and navigation patterns</li>
        </ul>

        <h3>How We Use Your Information</h3>
        <p>Your information is used for the following purposes:</p>
        <ul>
            <li>Processing and fulfilling your orders</li>
            <li>Communicating with you about orders and deliveries</li>
            <li>Providing customer support and responding to inquiries</li>
            <li>Improving our website and user experience</li>
            <li>Sending promotional offers and updates (with your consent)</li>
            <li>Preventing fraud and enhancing security</li>
            <li>Analyzing trends and understanding customer preferences</li>
        </ul>

        <h3>Information Sharing and Disclosure</h3>
        <p>We respect your privacy and do not sell your personal information. We may share your information only in the following circumstances:</p>
        <ul>
            <li><strong>Service Providers:</strong> With delivery partners and payment processors to fulfill orders</li>
            <li><strong>Legal Requirements:</strong> When required by law or to protect our rights</li>
            <li><strong>Business Transfers:</strong> In connection with mergers, acquisitions, or asset sales</li>
            <li><strong>With Your Consent:</strong> When you explicitly agree to share your information</li>
        </ul>

        <h3>Data Security</h3>
        <p>We implement appropriate technical and organizational measures to protect your personal information:</p>
        <ul>
            <li>Secure Socket Layer (SSL) encryption for data transmission</li>
            <li>Regular security audits and vulnerability assessments</li>
            <li>Access controls and authentication requirements</li>
            <li>Secure storage systems with backup procedures</li>
            <li>Employee training on data protection practices</li>
        </ul>

        <div class="highlight-box" style="background: #d1ecf1; border-left-color: #0c5460;">
            <i class="fas fa-lock" style="color: #0c5460;"></i>
            <strong>Security Note:</strong> While we strive to protect your information, no method of transmission over the internet is 100% secure. We cannot guarantee absolute security.
        </div>

        <h3>Your Privacy Rights</h3>
        <p>You have the following rights regarding your personal information:</p>
        <ul>
            <li><strong>Access:</strong> Request a copy of your personal data</li>
            <li><strong>Correction:</strong> Update or correct inaccurate information</li>
            <li><strong>Deletion:</strong> Request deletion of your personal data</li>
            <li><strong>Opt-Out:</strong> Unsubscribe from marketing communications</li>
            <li><strong>Data Portability:</strong> Receive your data in a structured format</li>
        </ul>
        <p>To exercise these rights, please contact us at <strong>chavezleeann@gmail.com</strong> or call <strong>+63 926 765 8075</strong>.</p>

        <h3>Cookies and Tracking Technologies</h3>
        <p>We use cookies and similar technologies to enhance your browsing experience:</p>
        <ul>
            <li><strong>Essential Cookies:</strong> Required for website functionality</li>
            <li><strong>Performance Cookies:</strong> Help us understand how visitors use our site</li>
            <li><strong>Functionality Cookies:</strong> Remember your preferences and settings</li>
        </ul>
        <p>You can control cookies through your browser settings, but disabling them may affect website functionality.</p>

        <h3>Third-Party Links</h3>
        <p>Our website may contain links to third-party websites. We are not responsible for the privacy practices of these external sites. We encourage you to read their privacy policies before providing any personal information.</p>

        <h3>Children's Privacy</h3>
        <p>Our services are not directed to individuals under the age of 18. We do not knowingly collect personal information from children. If you believe we have inadvertently collected such information, please contact us immediately.</p>

        <h3>Changes to This Privacy Policy</h3>
        <p>We may update this Privacy Policy periodically to reflect changes in our practices or legal requirements. We will notify you of significant changes by:</p>
        <ul>
            <li>Posting a notice on our website</li>
            <li>Sending an email to registered users</li>
            <li>Updating the "Last Updated" date at the top of this policy</li>
        </ul>

        <h3>Contact Us</h3>
        <p>If you have any questions or concerns about this Privacy Policy or our data practices, please contact us:</p>
        <ul>
            <li><i class="fas fa-map-marker-alt me-2"></i><strong>Address:</strong> 1127, Quezon City, Philippines</li>
            <li><i class="fas fa-phone me-2"></i><strong>Phone:</strong> +63 926 765 8075</li>
            <li><i class="fas fa-envelope me-2"></i><strong>Email:</strong> chavezleeann@gmail.com</li>
        </ul>
    </div>

    <div class="text-center">
        <a href="../user/index.php" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Shop
        </a>
    </div>
</div>

<?php require_once '../components/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;

    // Toggle Profile Dropdown
    function toggleProfile() {
        if (!isLoggedIn) {
            // If not logged in, redirect to login page since we are in Legal folder
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
        // Adjusted path for fetching cart count since we are in Legal folder
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