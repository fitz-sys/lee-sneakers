<?php
require_once '../config/database.php';

// Check if logged in
if (!isLoggedIn()) {
    redirect('../index.php');
}

// Redirect admins to admin panel
if (isAdmin()) {
    redirect('../admin/index.php');
}

$user_id = $_SESSION['user_id'];
$is_logged_in = isLoggedIn();

// --- FETCH BRANDS FOR NAVBAR ---
$brands_query = "SELECT DISTINCT name FROM brands ORDER BY name ASC";
$brands_result = $conn->query($brands_query);
$brands = [];
while ($brand_row = $brands_result->fetch_assoc()) {
    $brands[] = $brand_row['name'];
}

// Get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

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

// Handle address operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'add_address') {
            $address_data = json_encode([
                'first_name' => sanitize($_POST['first_name']),
                'last_name' => sanitize($_POST['last_name']),
                'address' => sanitize($_POST['address']),
                'apartment' => sanitize($_POST['apartment'] ?? ''),
                'postal_code' => sanitize($_POST['postal_code']),
                'city' => sanitize($_POST['city']),
                'region' => sanitize($_POST['region']),
                'phone' => sanitize($_POST['phone']),
                'is_default' => isset($_POST['is_default']) ? 1 : 0
            ]);
            
            $insert_address = "INSERT INTO user_addresses (user_id, address_data, is_default, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($insert_address);
            $is_default = isset($_POST['is_default']) ? 1 : 0;
            $stmt->bind_param("isi", $user_id, $address_data, $is_default);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Address added successfully!';
            } else {
                $_SESSION['error'] = 'Failed to add address.';
            }
            $stmt->close();
            redirect('profile.php');
        }
        
        if ($_POST['action'] === 'delete_address') {
            $address_id = intval($_POST['address_id']);
            $delete_query = "DELETE FROM user_addresses WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($delete_query);
            $stmt->bind_param("ii", $address_id, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Address deleted successfully!';
            } else {
                $_SESSION['error'] = 'Failed to delete address.';
            }
            $stmt->close();
            redirect('profile.php');
        }
        
        if ($_POST['action'] === 'set_default_address') {
            // First, unset all default addresses
            $unset_query = "UPDATE user_addresses SET is_default = 0 WHERE user_id = ?";
            $stmt = $conn->prepare($unset_query);
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();
            
            // Then set the new default
            $address_id = intval($_POST['address_id']);
            $set_default = "UPDATE user_addresses SET is_default = 1 WHERE id = ? AND user_id = ?";
            $stmt = $conn->prepare($set_default);
            $stmt->bind_param("ii", $address_id, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = 'Default address updated!';
            } else {
                $_SESSION['error'] = 'Failed to update default address.';
            }
            $stmt->close();
            redirect('profile.php');
        }
    }
}

// Get user addresses
$addresses_query = "SELECT * FROM user_addresses WHERE user_id = ? ORDER BY is_default DESC, created_at DESC";
$stmt = $conn->prepare($addresses_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$addresses = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - LEE Sneakers</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding-top: 100px;
            background: #f8f9fa;
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
            /* left: -40%;  <-- REMOVED THIS */
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
        .profile-container-nav {
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

        /* --- PROFILE SPECIFIC STYLES --- */
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 50px;
        }

        .profile-header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .profile-header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-info h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #000435;
            margin-bottom: 0.5rem;
        }

        .user-email {
            color: #666;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn {
            padding: 0.8rem 2rem;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logout-btn:hover {
            background: #bb2d3b;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
            color: white;
        }

        .section-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }

        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #000435;
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }

        .section-title i {
            color: #FEC700;
        }

        .address-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }

        .address-card {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            position: relative;
            transition: all 0.3s ease;
        }

        .address-card:hover {
            border-color: #FEC700;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .address-card.default {
            border-color: #28a745;
            background: #f8fff9;
        }

        .default-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .address-content {
            margin-bottom: 15px;
        }

        .address-name {
            font-weight: 700;
            color: #000435;
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .address-details {
            color: #666;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .address-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .address-actions button {
            padding: 6px 14px;
            border: none;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .set-default-btn {
            background: #007bff;
            color: white;
        }

        .set-default-btn:hover {
            background: #0056b3;
        }

        .delete-btn {
            background: #dc3545;
            color: white;
        }

        .delete-btn:hover {
            background: #bb2d3b;
        }

        .add-address-btn {
            padding: 0.8rem 1.5rem;
            background: #000435;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .add-address-btn:hover {
            background: #FEC700;
            color: #000435;
            transform: translateY(-2px);
        }

        .no-addresses {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }

        .no-addresses i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #ddd;
        }

        .order-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            border-left: 4px solid #FEC700;
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

        @media (max-width: 768px) {
            .profile-header-content {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .address-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
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
      <div class="profile-container-nav">
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

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-header-content">
                <div class="user-info">
                    <h2><?php echo htmlspecialchars($user['full_name']); ?></h2>
                    <div class="user-email">
                        <i class="fas fa-envelope"></i>
                        <?php echo htmlspecialchars($user['email']); ?>
                    </div>
                </div>
                <a href="../includes/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    Sign Out
                </a>
            </div>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-map-marker-alt"></i>
                    My Addresses
                </h3>
                <button class="add-address-btn" data-bs-toggle="modal" data-bs-target="#addAddressModal">
                    <i class="fas fa-plus me-2"></i>Add Address
                </button>
            </div>

            <?php if ($addresses->num_rows > 0): ?>
                <div class="address-grid">
                    <?php while ($address = $addresses->fetch_assoc()): ?>
                        <?php $addr_data = json_decode($address['address_data'], true); ?>
                        <div class="address-card <?php echo $address['is_default'] ? 'default' : ''; ?>">
                            <?php if ($address['is_default']): ?>
                                <span class="default-badge">Default</span>
                            <?php endif; ?>
                            
                            <div class="address-content">
                                <div class="address-name">
                                    <?php echo htmlspecialchars($addr_data['first_name'] . ' ' . $addr_data['last_name']); ?>
                                </div>
                                <div class="address-details">
                                    <?php echo htmlspecialchars($addr_data['address']); ?><br>
                                    <?php if (!empty($addr_data['apartment'])): ?>
                                        <?php echo htmlspecialchars($addr_data['apartment']); ?><br>
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($addr_data['city'] . ', ' . $addr_data['region']); ?><br>
                                    <?php echo htmlspecialchars($addr_data['postal_code']); ?><br>
                                    <i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($addr_data['phone']); ?>
                                </div>
                            </div>
                            
                            <div class="address-actions">
                                <?php if (!$address['is_default']): ?>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="set_default_address">
                                        <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                        <button type="submit" class="set-default-btn">Set as Default</button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this address?');">
                                    <input type="hidden" name="action" value="delete_address">
                                    <input type="hidden" name="address_id" value="<?php echo $address['id']; ?>">
                                    <button type="submit" class="delete-btn">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="no-addresses">
                    <i class="fas fa-map-marker-alt"></i>
                    <h5>No addresses added</h5>
                    <p>Add your first delivery address</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="section-card">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-shopping-bag"></i>
                    My Orders
                </h3>
            </div>

            <?php if ($orders->num_rows > 0): ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
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
                                    <?php if (in_array($order['status'], ['pending', 'confirmed'])): ?>
                                        <form method="POST" action="cancel_order.php" onsubmit="return confirm('Are you sure you want to cancel this order? Stock will be restored.');">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" class="btn btn-outline-danger w-100">
                                                <i class="fas fa-times me-2"></i>Cancel Order
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
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

        <div class="section-card">
            <div class="section-header">
                <h3 class="section-title">
                    <i class="fas fa-cog"></i>
                    Account Settings
                </h3>
            </div>
            
            <div class="alert alert-danger">
                <h6><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h6>
                <p class="mb-3">Once you delete your account, there is no going back. Please be certain.</p>
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                    <i class="fas fa-trash me-2"></i>Delete My Account
                </button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Delete Account</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="delete_account.php">
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Warning!</strong> This action cannot be undone.
                        </div>
                        
                        <p>Deleting your account will:</p>
                        <ul>
                            <li>Permanently delete your profile</li>
                            <li>Remove all your saved addresses</li>
                            <li>Clear your shopping cart</li>
                            <li>You will not be able to view your order history</li>
                        </ul>
                        
                        <p class="mb-3"><strong>Note:</strong> You cannot delete your account if you have pending orders.</p>
                        
                        <div class="mb-3">
                            <label class="form-label">Enter your password to confirm:</label>
                            <input type="password" class="form-control" name="password" required placeholder="Your password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash me-2"></i>Yes, Delete My Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="addAddressModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Address</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="action" value="add_address">
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_default" id="is_default">
                                <label class="form-check-label" for="is_default">
                                    Set as default address
                                </label>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="first_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="last_name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address *</label>
                            <input type="text" class="form-control" name="address" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apartment, suite, etc. (optional)</label>
                            <input type="text" class="form-control" name="apartment">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Postal Code *</label>
                                <input type="text" class="form-control" name="postal_code" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City *</label>
                                <input type="text" class="form-control" name="city" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Region *</label>
                            <select class="form-select" name="region" required>
                                <option value="">Select Region</option>
                                <option value="Abra">Abra</option>
                                <option value="Agusan del Norte">Agusan del Norte</option>
                                <option value="Agusan del Sur">Agusan del Sur</option>
                                <option value="Metro Manila">Metro Manila</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone *</label>
                            <input type="tel" class="form-control" name="phone" placeholder="+63" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-dark">Save Address</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Update Cart Badge
        function updateCartBadge() {
            fetch('get_cart_count.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.cart_count > 0) {
                        document.getElementById('cartBadge').textContent = data.cart_count;
                        document.getElementById('cartBadge').style.display = 'block';
                    }
                })
                .catch(error => console.error('Error updating cart badge:', error));
        }

        // Toggle Profile Menu
        function toggleProfile() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.closest('.profile-container-nav')) {
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

        document.addEventListener('DOMContentLoaded', function() {
            updateCartBadge();
        });
    </script>
</body>
</html>