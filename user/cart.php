<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('../index.php');
}

// Define $is_logged_in for use in the navbar
$is_logged_in = isLoggedIn();

// Redirect admins to admin panel
if (isAdmin()) {
    redirect('../admin/index.php');
}

$user_id = $_SESSION['user_id'];

// Get cart items with product details
$cart_query = "SELECT 
                c.id as cart_id,
                c.quantity,
                c.size,
                c.price,
                p.id as product_id,
                p.name as product_name,
                p.category,
                pv.id as variant_id,
                pv.image,
                pv.stock
               FROM cart c
               JOIN products p ON c.product_id = p.id
               JOIN product_variants pv ON c.variant_id = pv.id
               WHERE c.user_id = ?
               ORDER BY c.created_at DESC";

$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
$stmt->close();

// Calculate totals
$subtotal = 0;
while ($item = $cart_items->fetch_assoc()) {
    $subtotal += $item['price'] * $item['quantity'];
    // Reset internal pointer to allow re-fetching in the HTML section
    $cart_items->data_seek(0);
}

// --- FETCH BRANDS FOR NAVBAR (Added to match index.php) ---
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
    <title>Shopping Cart - LEE Sneakers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Preserved Body Color (Default: white/light) */
        body {
            background-color: #f8f9fa; /* Preserved original background */
            padding-top: 100px; /* Adjusted for fixed navbar */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

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

        /* --- CART SPECIFIC STYLES --- */
        .cart-header {
            background: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .cart-header h2 {
            font-weight: 700;
            color: #000435;
            margin: 0;
            font-size: 1.8rem;
        }

        .cart-table-header {
            background: white;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            color: #666;
            letter-spacing: 0.5px;
        }

        .cart-item {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            transition: box-shadow 0.3s ease;
        }

        .cart-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .cart-item-image {
            width: 90px;
            height: 90px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #f0f0f0;
        }

        .product-details {
            flex: 1;
        }

        .product-name {
            font-weight: 600;
            font-size: 1rem;
            color: #000435;
            margin-bottom: 0.5rem;
            line-height: 1.4;
        }

        .product-meta {
            display: flex;
            gap: 1rem;
            font-size: 0.85rem;
            color: #666;
        }

        .product-meta span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .size-badge {
            background: #f0f0f0;
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-weight: 500;
        }

        .price-col {
            font-weight: 600;
            font-size: 1.1rem;
            color: #000435;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            border: 1px solid #ddd;
            border-radius: 6px;
            overflow: hidden;
            width: fit-content;
        }

        .quantity-btn {
            background: #f8f9fa;
            border: none;
            padding: 0.5rem 0.8rem;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 1rem;
            color: #666;
        }

        .quantity-btn:hover {
            background: #e9ecef;
            color: #000;
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            border: none;
            border-left: 1px solid #ddd;
            border-right: 1px solid #ddd;
            padding: 0.5rem 0.3rem;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .quantity-input:focus {
            outline: none;
        }

        .total-col {
            font-weight: 700;
            font-size: 1.2rem;
            color: #000435;
        }

        .remove-btn {
            background: transparent;
            border: none;
            color: #999;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0.5rem;
            transition: all 0.2s;
        }

        .remove-btn:hover {
            color: #dc3545;
            transform: scale(1.1);
        }

        /* Order Summary */
        .order-summary {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            position: sticky;
            top: 100px;
        }

        .order-summary h4 {
            font-weight: 700;
            font-size: 1.3rem;
            color: #000435;
            margin-bottom: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.8rem 0;
            font-size: 0.95rem;
            color: #666;
        }

        .summary-row.total {
            border-top: 2px solid #f0f0f0;
            margin-top: 1rem;
            padding-top: 1rem;
            font-size: 1.3rem;
            font-weight: 700;
            color: #000435;
        }

        .checkout-btn {
            width: 100%;
            padding: 1rem;
            background: #000435;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 1rem;
        }

        .checkout-btn:hover {
            background: #FEC700;
            color: #000435;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .continue-shopping-btn {
            width: 100%;
            padding: 1rem;
            background: white;
            color: #000435;
            border: 2px solid #000435;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .continue-shopping-btn:hover {
            background: #000435;
            color: white;
        }

        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 8px;
        }
        
        .empty-cart i {
            font-size: 5rem;
            color: #e0e0e0;
            margin-bottom: 1.5rem;
        }

        .empty-cart h3 {
            font-weight: 600;
            color: #000435;
            margin-bottom: 0.5rem;
        }

        .empty-cart p {
            color: #999;
            margin-bottom: 2rem;
        }

        .empty-cart .btn {
            padding: 0.8rem 2.5rem;
            background: #000435;
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
        }

        .empty-cart .btn:hover {
            background: #FEC700;
            color: #000435;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .cart-table-header {
                display: none;
            }

            .cart-item {
                padding: 1rem;
            }

            .product-name {
                font-size: 0.9rem;
            }

            .price-col, .total-col {
                font-size: 1rem;
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

    <div class="cart-header">
        <div class="container">
            <h2>Shopping Cart</h2>
        </div>
    </div>

    <div class="container pb-5">
        <?php if ($cart_items->num_rows > 0): ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="cart-table-header d-none d-md-block">
                        <div class="row align-items-center">
                            <div class="col-md-5">Product</div>
                            <div class="col-md-2">Price</div>
                            <div class="col-md-2">Quantity</div>
                            <div class="col-md-2">Total</div>
                            <div class="col-md-1"></div>
                        </div>
                    </div>

                    <?php while ($item = $cart_items->fetch_assoc()): ?>
                        <?php 
                        $item_total = $item['price'] * $item['quantity'];
                        // $subtotal is already calculated at top, but we can update item totals visually
                        ?>
                        <div class="cart-item">
                            <div class="row align-items-center">
                                <div class="col-md-5">
                                    <div class="d-flex gap-3">
                                        <img src="../uploads/products/<?php echo $item['image']; ?>" 
                                             alt="<?php echo htmlspecialchars($item['product_name']); ?>" 
                                             class="cart-item-image">
                                        <div class="product-details">
                                            <div class="product-name">
                                                <?php echo htmlspecialchars($item['product_name']); ?>
                                            </div>
                                            <div class="product-meta">
                                                <span>
                                                    <i class="fas fa-tag"></i>
                                                    <?php echo htmlspecialchars($item['category']); ?>
                                                </span>
                                                <span>
                                                    Size: <span class="size-badge"><?php echo $item['size']; ?></span>
                                                </span>
                                            </div>
                                            <div class="product-meta mt-1">
                                                <span style="color: <?php echo $item['stock'] > 10 ? '#28a745' : '#dc3545'; ?>">
                                                    <i class="fas fa-box"></i>
                                                    <?php echo $item['stock']; ?> in stock
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-2 col-6 mt-3 mt-md-0">
                                    <div class="d-md-none text-muted small mb-1">Price:</div>
                                    <div class="price-col"><?php echo formatPrice($item['price']); ?></div>
                                </div>

                                <div class="col-md-2 col-6 mt-3 mt-md-0">
                                    <div class="d-md-none text-muted small mb-1">Quantity:</div>
                                    <div class="quantity-controls">
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] - 1; ?>, <?php echo $item['stock']; ?>)">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" 
                                               class="quantity-input" 
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" 
                                               max="<?php echo $item['stock']; ?>"
                                               onchange="updateQuantity(<?php echo $item['cart_id']; ?>, this.value, <?php echo $item['stock']; ?>)"
                                               readonly>
                                        <button class="quantity-btn" onclick="updateQuantity(<?php echo $item['cart_id']; ?>, <?php echo $item['quantity'] + 1; ?>, <?php echo $item['stock']; ?>)">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-2 col-6 mt-3 mt-md-0">
                                    <div class="d-md-none text-muted small mb-1">Total:</div>
                                    <div class="total-col"><?php echo formatPrice($item_total); ?></div>
                                </div>

                                <div class="col-md-1 col-6 mt-3 mt-md-0 text-end">
                                    <button class="remove-btn" onclick="removeItem(<?php echo $item['cart_id']; ?>)" title="Remove item">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="col-lg-4">
                    <div class="order-summary">
                        <h4>Order Summary</h4>
                        
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <strong><?php echo formatPrice($subtotal); ?></strong>
                        </div>

                        <div class="summary-row total">
                            <span>TOTAL:</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>

                        <div class="mt-3">
                            <small class="text-muted d-block mb-3">Tax included and shipping calculated at checkout</small>
                        </div>

                        <button class="checkout-btn" onclick="window.location.href='checkout.php'">
                            Proceed to Checkout
                        </button>
                        
                        <button class="continue-shopping-btn" onclick="window.location.href='index.php'">
                            Continue Shopping
                        </button>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p>Add some products to get started!</p>
                <a href="index.php" class="btn">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
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

        // Update Cart Badge
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


        // Update Quantity
        function updateQuantity(cartId, quantity, maxStock) {
            if (quantity < 1) return;
            if (quantity > maxStock) {
                alert(`Cannot add more than ${maxStock} units (max stock available).`);
                return;
            }
            
            fetch('update_cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({cart_id: cartId, quantity: quantity})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to update quantity');
            });
        }

        // Remove Item
        function removeItem(cartId) {
            if (!confirm('Remove this item from cart?')) return;
            
            fetch('remove_from_cart.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({cart_id: cartId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to remove item');
            });
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Update cart badge
            updateCartBadge();
            
        });
    </script>
</body>
</html>