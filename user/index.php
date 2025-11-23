<?php
require_once '../config/database.php';

// Check if user is logged in
$is_logged_in = isLoggedIn();
$username = $is_logged_in ? $_SESSION['username'] : '';


// Get products by category with their variants
function getProductsWithVariants($conn, $category) {
    $query = "SELECT * FROM products WHERE FIND_IN_SET(?, category) > 0 ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
   
    while ($product = $result->fetch_assoc()) {
        $variant_query = "SELECT id, image, size, stock FROM product_variants
                         WHERE product_id = ? ORDER BY variant_order";
        $variant_stmt = $conn->prepare($variant_query);
        $variant_stmt->bind_param("i", $product['id']);
        $variant_stmt->execute();
        $variant_result = $variant_stmt->get_result();
       
        $variants = [];
        while ($variant = $variant_result->fetch_assoc()) {
            $variants[] = $variant;
        }
        $variant_stmt->close();
       
        $product['variants'] = $variants;
        $products[] = $product;
    }
   
    $stmt->close();
    return $products;
}


// Get products by brand
function getProductsByBrand($conn, $brand) {
    $query = "SELECT * FROM products WHERE brand = ? ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $brand);
    $stmt->execute();
    $result = $stmt->get_result();
    $products = [];
   
    while ($product = $result->fetch_assoc()) {
        $variant_query = "SELECT id, image, size, stock FROM product_variants
                         WHERE product_id = ? ORDER BY variant_order";
        $variant_stmt = $conn->prepare($variant_query);
        $variant_stmt->bind_param("i", $product['id']);
        $variant_stmt->execute();
        $variant_result = $variant_stmt->get_result();
       
        $variants = [];
        while ($variant = $variant_result->fetch_assoc()) {
            $variants[] = $variant;
        }
        $variant_stmt->close();
       
        $product['variants'] = $variants;
        $products[] = $product;
    }
   
    $stmt->close();
    return $products;
}


$new_arrivals = getProductsWithVariants($conn, 'New Arrivals');
$bestseller = getProductsWithVariants($conn, 'Best Seller');
$basketball_shoes = getProductsWithVariants($conn, 'Basketball Shoes');
$running_shoes_men = getProductsWithVariants($conn, 'Running Shoes Men');
$lifestyle_men = getProductsWithVariants($conn, 'Lifestyle Men');
$running_shoes_women = getProductsWithVariants($conn, 'Running Shoes Women');
$lifestyle_women = getProductsWithVariants($conn, 'Lifestyle Women');
$kids_products = getProductsWithVariants($conn, 'Kids');

// Update the brands array to get from database
$brands_query = "SELECT DISTINCT name FROM brands ORDER BY name ASC";
$brands_result = $conn->query($brands_query);
$brands = []; // Initialize array

// Kunin ang brands mula sa database
while ($brand_row = $brands_result->fetch_assoc()) {
    $brands[] = $brand_row['name'];
}

// Get products for each brand (Dynamic na ito ngayon)
$brand_products = [];
foreach ($brands as $brand) {
    $brand_products[$brand] = getProductsByBrand($conn, $brand);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LEE Sneakers - Premium Collection</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        /* Navigation Styles - Sole Academy Inspired */
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
            left: -40%;
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

        .navbar-nav .nav-item:has(#menDropdown) {
            margin-left: -1rem;  /* Pulls MEN very close to BEST SELLER */
            margin-right: 0rem;
        }

        .navbar-nav .nav-item:has(#womenDropdown) {
            margin-left: 0.6rem;
        }

        .navbar-nav .nav-item:has([href="#kids"]) {
            margin-left: -1rem;  /* Pulls KIDS very close to WOMEN */
            margin-right: 1rem;
        }

        .navbar-nav .nav-item:has([href="#brands"]) {
            margin-left: -1.5rem;
        }  
       
        .navbar-nav .nav-link:hover {
            color: #fff;
            background: rgba(254, 199, 0, 0.1);
            border-radius: 4px;
        }
       
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
        }
       
        .nav-icons a:hover {
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
       
        /* Search Styles - Yellow Outline */
        .search-container {
            position: relative;
            display: flex;
            align-items: center;
        }

        .search-input {
            width: 220px;
            padding: 10px 40px 10px 18px;
            border: 2px solid #FEC700; /* yellow outline */
            border-radius: 50px;
            font-size: 13px;
            transition: all 0.3s ease;
            outline: none;
            background: transparent; /* no fill */
            color: #FEC700; /* yellow text */
        }

        .search-input::placeholder {
            color: rgba(254, 199, 0, 0.7);
        }

        .search-input:focus {
            border-color: #fff;
            box-shadow: 0 0 6px rgba(254, 199, 0, 0.4);
        }

        /* Button (icon only, no filled background) */
        .search-btn {
            position: absolute;
            right: 20px;
            background: transparent;
            border: none;
            color: #FEC700;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.3s ease;
        }

        .search-btn i {
            color: #FEC700 !important; /* yellow icon */
        }

        .search-btn:hover i {
            color: #fff; /* optional white hover */
        }

        /* Profile Dropdown (smaller version) */
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
            min-width: 180px; /* reduced width */
            display: none;
            z-index: 1000;
        }

        .profile-dropdown.show {
            display: block;
        }

        .profile-dropdown a {
            display: flex;
            align-items: center;
            gap: 8px; /* space between icon and text */
            padding: 0.5rem 1rem; /* smaller padding */
            color: #333;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem; /* smaller text */
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
       
        .offcanvas-body {
            padding: 0;
            background: #000435;
        }
       
        .mobile-search-section {
            background: rgba(254, 199, 0, 0.05);
            border-bottom: 1px solid rgba(254, 199, 0, 0.2);
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
       
        .accordion-button:focus {
            box-shadow: none;
            border-color: #FEC700;
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
       
        .mobile-auth-section {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1.5rem;
            background: rgba(254, 199, 0, 0.05);
            border-top: 3px solid #FEC700;
        }
       
        .mobile-auth-section .btn {
            width: 100%;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0.8rem;
        }
       
        .mobile-auth-section .btn-primary {
            background: #FEC700;
            border-color: #FEC700;
            color: #000435;
        }
       
        .mobile-auth-section .btn-primary:hover {
            background: #fff;
            border-color: #fff;
            color: #000435;
        }
       
        .mobile-auth-section .btn-outline-primary {
            border-color: #FEC700;
            color: #FEC700;
        }
       
        .mobile-auth-section .btn-outline-primary:hover {
            background: #FEC700;
            color: #000435;
            border-color: #FEC700;
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
       
        @media (min-width: 992px) {
            .container-fluid {
                max-width: 1400px;
            }
        }

        .product-card {
            position: relative;
            transition: transform 0.3s ease;
        }
        .product-card:hover {
            transform: translateY(-5px);
        }
        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 10;
            pointer-events: none;
        }
        .product-card:hover .product-overlay {
            opacity: 1;
            pointer-events: auto;
        }

        .product-title {
            padding: 0 20px;
            margin: 0 0 8px 0;  /* Small bottom margin only */
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            min-height: auto;  /* Changed from 48px to auto */
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.3;  /* Tighter line height */
        }

        .product-brand {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0 20px;
            margin-bottom: 5px;
            margin-top: 0;  /* Add this to remove any top spacing */
        }

        .brand-logo-small {
            width: 30px;
            height: 20px;
            object-fit: contain;
        }

        .product-brand span {
            color: #666;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .product-rating {
            padding: 0 20px;
            margin-bottom: 8px;  /* Reduced gap */
        }

        .product-price {
            padding: 0 20px;
            margin: 0;
            font-size: 1.1rem;
            font-weight: bold;
            color: #333;
        }

        .product-card img {
            display: block;
            margin-bottom: 1px;  /* Gap between image and title */
        }

        .quick-view-btn {
            background: white;
            color: #333;
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .quick-view-btn:hover {
            background: #333;
            color: white;
            transform: scale(1.05);
        }
        .modal-product-image {
            width: 100%;
            height: auto;
            max-height: 500px;
            object-fit: contain;
            border-radius: 8px;
            cursor: pointer;
            background: #f8f9fa;
        }
        .variant-thumbnail {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 3px solid transparent;
            transition: all 0.3s ease;
            margin: 5px;
        }
        .variant-thumbnail:hover {
            border-color: #666;
            transform: scale(1.05);
        }
        .variant-thumbnail.active {
            border-color: #333;
            box-shadow: 0 0 0 2px white, 0 0 0 4px #333;
        }
        .size-option {
            display: inline-block;
            padding: 8px 16px;
            margin: 5px;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            user-select: none;
        }
        .size-option:hover {
            border-color: #333;
            background: #f8f9fa;
        }
        .size-option.selected {
            border-color: #333;
            background: #333;
            color: white;
        }
        .size-option.disabled {
            opacity: 0.3;
            cursor: not-allowed;
            pointer-events: none;
            text-decoration: line-through;
        }
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .quantity-btn {
            width: 35px;
            height: 35px;
            border: 1px solid #ddd;
            background: white;
            cursor: pointer;
            border-radius: 5px;
            font-weight: bold;
        }
        .quantity-input {
            width: 60px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 5px;
        }
        .stock-badge {
            background: #28a745;
            color: white;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            display: inline-block;
        }
        .stock-badge.low {
            background: #ffc107;
        }
        .stock-badge.out {
            background: #dc3545;
        }
        .variant-selector-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .slider-section {
            padding: 0;
            text-align: center;
            max-width: 100%;
            overflow: hidden;
            margin-top: 75px;
            margin-bottom: 30px;
        }
        #heroCarousel {
            margin: 0 auto;
        }
        .carousel-item {
            transition: transform 1s ease-in-out;
        }
        .banner-image {
            width: 100%;
            height: auto;
            min-height: 500px;
            max-height: 650px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .carousel-item a {
            display: block;
            position: relative;
            text-decoration: none;
        }

        .carousel-item a:hover .banner-image {
            transform: scale(1.02);
        }

        @media (max-width: 768px) {
            .banner-image {
                min-height: 300px;
                max-height: 400px;
            }
        }

        .brand-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 2px solid #f0f0f0;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }

        .brand-card .brand-logo {
            max-width: 150px;
            max-height: 100px;
            width: auto;
            height: auto;
            object-fit: contain;
            transition: all 0.3s ease;
        }

        .brand-card:hover .brand-logo {
            transform: scale(1.1);
        }
        .brand-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border-color: #FEC700;
        }

        /* Mid-Page Banner Styles */
        .mid-page-banner-section {
            width: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .mid-page-banner-wrapper {
            width: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #000435;
        }

        .mid-page-banner-wrapper a {
            display: block;
            width: 100%;
            text-decoration: none;
            position: relative;
        }

        .mid-page-banner-image {
            width: 100%;
            height: auto;
            display: block;
            object-fit: contain;
            max-width: 100%;
            transition: transform 0.3s ease;
        }

        .mid-page-banner-wrapper a:hover .mid-page-banner-image {
            transform: scale(1.02);
        }

        @media (max-width: 768px) {
            .mid-page-banner-section {
                margin: 30px 0;
            }
        }

        #new-arrivals {
            padding-bottom: 10px !important; /* Reduce space after New Arrivals */
        }

        #best-seller {
            padding-top: 5px !important; /* Reduce space before Best Seller */
        }

        .brand-card h5 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #333;
            margin: 0;
        }
       
        /* Login Modal */
        .login-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            align-items: center;
            justify-content: center;
        }
       
        .login-modal.active {
            display: flex;
        }
       
        .login-modal-content {
            background: white;
            padding: 40px;
            border-radius: 10px;
            max-width: 450px;
            width: 90%;
            position: relative;
        }
       
        .login-modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }
       
        .login-modal-close:hover {
            color: #333;
        }
       
        .hidden {
            display: none;
        }

        /* Brand Banner Styles */
        .brand-banner {
            width: 100%;
            margin-top: 80px;
            display: none;
            background: #000435;
        }

        .brand-banner.active {
            display: block;
        }

        .brand-banner img {
            width: 100%;
            height: auto;
            display: block;
        }

        @media (max-width: 768px) {
            .brand-banner {
                margin-top: 70px;
            }
        }

        /* Brand Carousel Section */
        .brand-carousel-section {
            background: #fec700;
            padding: 40px 0;
            overflow: hidden;
            position: relative;
            margin-top: 40px;
            width: 100vw;
            margin-left: calc(-50vw + 50%);
        }

        .brand-carousel-wrapper {
            overflow: hidden;
            position: relative;
            width: 100%;
        }

        .brand-carousel-track {
            display: flex;
            gap: 2px;
            animation: scroll 30s linear infinite;
            width: fit-content;
        }

        .brand-carousel-track:hover {
            animation-play-state: paused;
        }

        .brand-logo-item {
            flex-shrink: 0;
            background: #fec700;
            padding: 30px 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 200px;
            height: 100px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .brand-logo-item:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(254, 199, 0, 0.4);
            background: #FEC700;
        }

        .brand-logo-item img {
            max-width: 150px;
            max-height: 100px;
            width: auto;
            height: auto;
            object-fit: contain;
            filter: grayscale(0%);
            transition: all 0.3s ease;
        }

        .brand-logo-item:hover img {
            filter: brightness(1.1);
        }

        @keyframes scroll {
            0% {
                transform: translateX(0);
            }
            100% {
                transform: translateX(-50%);
            }
        }

        /* ===== BACK TO TOP BUTTON ===== */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #FEC700;
            color: #000435;
            border: none;
            border-radius: 50%;
            font-size: 1.5rem;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1001;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        .back-to-top:hover {
            background: #000435;
            color: #FEC700;
            transform: translateY(-5px);
            box-shadow: 0 6px 16px rgba(254, 199, 0, 0.4);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .brand-carousel-title {
                font-size: 1.8rem;
                margin-bottom: 30px;
            }
            
            .brand-carousel-track {
                gap: 40px;
                animation: scroll 20s linear infinite;
            }
            
            .brand-logo-item {
                min-width: 150px;
                height: 100px;
                padding: 20px 30px;
            }
            
            .brand-logo-item img {
                max-width: 120px;
                max-height: 60px;
            }

                .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
                font-size: 1.3rem;
            }
        }

        /* Chatbot Container */
        .chatbot-widget {
        position: fixed;
        bottom: 100px;
        right: 30px;
        z-index: 9999;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        transition: all 0.3s ease;
        }

        @media (max-width: 768px) {
            .chatbot-widget {
                bottom: 85px;
                right: 20px;
            }
        }

        /* Chat Bubble Button */
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

        /* Chat Window - FIXED */
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

        .chat-back-btn {
            background: none;
            border: none;
            color: #000435;
            font-size: 18px;
            cursor: pointer;
            transition: transform 0.2s;
            padding: 4px 8px;
        }

        .chat-back-btn:hover {
            transform: scale(1.2);
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

        /* Chat Messages - FIXED with proper scrolling */
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

        /* Quick Replies Container - FIXED positioning */
        #quickRepliesContainer {
            padding: 12px 16px;
            background: white;
            border-top: 1px solid #e0e0e0;
            flex-shrink: 0;
            overflow-y: auto;
            max-height: 250px;
        }

        .quick-replies {
            display: flex;
            flex-direction: column;
            gap: 8px;
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
            white-space: normal;
        }

        .quick-reply-btn:hover {
            background: #FEC700;
            color: #000435;
        }

        /* Chat Input - FIXED */
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

        /* Greeting Screen */
        .chat-greeting {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 20px;
            text-align: center;
        }

        .greeting-icon {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .greeting-title {
            font-size: 16px;
            font-weight: 700;
            color: #000435;
            margin-bottom: 8px;
        }

        .greeting-text {
            font-size: 13px;
            color: #666;
            margin-bottom: 20px;
            line-height: 1.4;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .chat-window {
                width: 100vw;
                height: 100vh;
                bottom: 0;
                right: 0;
                border-radius: 0;
                max-height: 100vh;
            }

            .chat-bubble-btn {
                width: 50px;
                height: 50px;
                font-size: 20px;
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


    <a class="navbar-brand" href="#">
      <img src="../nav_images/logo.png" alt="LEE Sneakers Logo">
    </a>

<ul class="navbar-nav d-none d-lg-flex flex-row align-items-center">
  <li class="nav-item">
    <a class="nav-link" href="#best-seller">BEST SELLER</a>
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="menDropdown" role="button" data-bs-toggle="dropdown">
       MEN
    </a>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item" href="#basketball-shoes">BASKETBALL SHOES</a></li>
      <li><a class="dropdown-item" href="#running-shoes-men">RUNNING SHOES</a></li>
      <li><a class="dropdown-item" href="#lifestyle-men">LIFESTYLE</a></li>
    </ul>
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="womenDropdown" role="button" data-bs-toggle="dropdown">
      WOMEN
    </a>
    <ul class="dropdown-menu">
      <li><a class="dropdown-item" href="#running-shoes-women">RUNNING SHOES</a></li>
      <li><a class="dropdown-item" href="#lifestyle-women">LIFESTYLE</a></li>
    </ul>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="#kids">KIDS</a>
  </li>
  <li class="nav-item dropdown">
    <a class="nav-link dropdown-toggle" href="#" id="brandsDropdown" role="button" data-bs-toggle="dropdown">
      BRANDS
    </a>
    <ul class="dropdown-menu">
      <?php foreach ($brands as $brand): 
        $brand_slug = strtolower(str_replace(' ', '', $brand));
      ?>
      <li><a class="dropdown-item" href="#brand-<?php echo $brand_slug; ?>"><?php echo strtoupper($brand); ?></a></li>
      <?php endforeach; ?>
    </ul>
  </li>
  <li class="nav-item">
  </li>
</ul>


    <div class="nav-icons ms-auto">
      <div class="search-container d-none d-lg-flex">
        <input type="text" class="search-input" placeholder="Search..." id="searchInput" onkeypress="if(event.key==='Enter') performSearch()">
        <button class="search-btn" onclick="performSearch()">
          <i class="fas fa-search"></i>
        </button>
      </div>
     
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

<div id="underarmourBanner" class="brand-banner">
    <img src="../banner_images/underarmour-banner.png" alt="Under Armour Banner">
</div>

<div id="nikeBanner" class="brand-banner">
    <img src="../banner_images/nike-banner.png" alt="Nike Banner">
</div>

<div id="adidasBanner" class="brand-banner">
    <img src="../banner_images/adidas-banner.png" alt="Adidas Banner">
</div>

<div id="pumaBanner" class="brand-banner">
    <img src="../banner_images/puma-banner.png" alt="Puma Banner">
</div>

<div id="newbalanceBanner" class="brand-banner">
    <img src="../banner_images/newbalance-banner.png" alt="New Balance Banner">
</div>

<div id="asicsBanner" class="brand-banner">
    <img src="../banner_images/asics-banner.png" alt="Asics Banner">
</div>

<div id="onitsukatigerBanner" class="brand-banner">
    <img src="../banner_images/onitsukatiger-banner.png" alt="Onitsuka Tiger Banner">
</div>

<div id="vansBanner" class="brand-banner">
    <img src="../banner_images/vans-banner.png" alt="Vans Banner">
</div>

<div id="hokaBanner" class="brand-banner">
    <img src="../banner_images/hoka-banner.png" alt="Hoka Banner">
</div>

<div id="jordanBanner" class="brand-banner">
    <img src="../banner_images/jordan-banner.png" alt="Jordan Banner">
</div>

<div id="underarmourBanner" class="brand-banner">
    <img src="../banner_images/underarmour-banner.png" alt="Under Armour Banner">
</div>

<div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">MENU</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body">
        <div class="mobile-search-section p-3">
            <div class="search-container">
                <input type="text" class="search-input w-100" placeholder="Search..." id="searchInputMobile" onkeypress="if(event.key==='Enter') performSearch()">
                <button class="search-btn" onclick="performSearch()">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>


<div class="mobile-menu-section">
    <a class="nav-link" href="#best-seller" data-bs-dismiss="offcanvas">BEST SELLER</a>
   
    <div class="accordion accordion-flush" id="mobileAccordion">
        <div class="accordion-item">
            <h2 class="accordion-header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#menCollapse">
                    FOR MEN
                </button>
            </h2>
            <div id="menCollapse" class="accordion-collapse collapse" data-bs-parent="#mobileAccordion">
                <div class="accordion-body p-0">
                    <a class="nav-link ps-4" href="#basketball-shoes" data-bs-dismiss="offcanvas">BASKETBALL SHOES</a>
                    <a class="nav-link ps-4" href="#running-shoes-men" data-bs-dismiss="offcanvas">RUNNING SHOES</a>
                    <a class="nav-link ps-4" href="#lifestyle-men" data-bs-dismiss="offcanvas">LIFESTYLE</a>
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
                    <a class="nav-link ps-4" href="#running-shoes-women" data-bs-dismiss="offcanvas">RUNNING SHOES</a>
                    <a class="nav-link ps-4" href="#lifestyle-women" data-bs-dismiss="offcanvas">LIFESTYLE</a>
                </div>
            </div>
        </div>
        
        <a class="nav-link" href="#kids" data-bs-dismiss="offcanvas">FOR KIDS</a>
        
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
                    <a class="nav-link ps-4" href="#brand-<?php echo $brand_slug; ?>" data-bs-dismiss="offcanvas"><?php echo strtoupper($brand); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
   
    <a class="nav-link" href="about.php" data-bs-dismiss="offcanvas">ABOUT US</a>
</div>
       
        <?php if (!$is_logged_in): ?>
        <div class="mobile-auth-section">
            <button class="btn btn-primary mb-2" onclick="openLoginModal()">SIGN IN</button>
            <button class="btn btn-outline-primary" onclick="openSignupModal()">CREATE ACCOUNT</button>
        </div>
        <?php endif; ?>
    </div>
</div>


<div class="login-modal" id="loginModal">
    <div class="login-modal-content">
        <i class="fas fa-times login-modal-close" onclick="closeLoginModal()"></i>
       
        <div id="loginFormContainer">
            <div class="text-center mb-4">
                <h3 class="fw-bold">Welcome Back</h3>
                <p class="text-muted">Sign in to your account</p>
            </div>
            <form action="../includes/login.php" method="POST">
                <div class="mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
                <button type="submit" class="btn btn-dark w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>Login
                </button>
            </form>
            <div class="text-center">
                <p class="text-muted">Don't have an account?
                    <a href="javascript:void(0)" onclick="switchToSignup()" class="text-decoration-none fw-bold" style="color: #FEC700;">Sign Up</a>
                </p>
            </div>
        </div>
       
        <div id="signupFormContainer" class="hidden">
            <div class="text-center mb-4">
                <h3 class="fw-bold">Create Account</h3>
                <p class="text-muted">Join us today</p>
            </div>
            <form action="../includes/signup.php" method="POST">
                <div class="mb-3">
                    <input type="text" class="form-control" name="full_name" placeholder="Full Name" required>
                </div>
                <div class="mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                </div>
                <div class="mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
                <div class="mb-3">
                    <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
                </div>
                <button type="submit" class="btn btn-dark w-100 mb-3">
                    <i class="fas fa-user-plus me-2"></i>Sign Up
                </button>
            </form>
            <div class="text-center">
                <p class="text-muted">Already have an account?
                    <a href="javascript:void(0)" onclick="switchToLogin()" class="text-decoration-none fw-bold" style="color: #FEC700;">Login</a>
                </p>
            </div>
        </div>
    </div>
</div>


<section class="slider-section" id="home">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
        <div class="carousel-inner">
            <div class="carousel-item active">
                <a href="#home" onclick="event.preventDefault(); showHomeView();" style="cursor: pointer;">
                    <img src="../banner_images/main-banner.png" alt="LEE Sneakers Main Banner" class="banner-image">
                </a>
            </div>
            <div class="carousel-item">
                <a href="#new-arrivals" onclick="event.preventDefault(); showSectionView('new-arrivals');" style="cursor: pointer;">
                    <img src="../banner_images/new-arrivals-banner.png" alt="New Arrivals" class="banner-image">
                </a>
            </div>
            <div class="carousel-item">
                <a href="#basketball-shoes" onclick="event.preventDefault(); showSectionView('basketball-shoes');" style="cursor: pointer;">
                    <img src="../banner_images/basketball-banner.png" alt="Basketball Shoes" class="banner-image">
                </a>
            </div>
            <div class="carousel-item">
                <a href="#running-shoes-men" onclick="event.preventDefault(); showSectionView('running-shoes-men');" style="cursor: pointer;">
                    <img src="../banner_images/running-banner.png" alt="Running Shoes" class="banner-image">
                </a>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</section>

<section class="mid-page-banner-section" id="mid-page-banner">
    <div class="mid-page-banner-wrapper">
<a href="#lifestyle-men" onclick="event.preventDefault(); showSectionView('lifestyle-men');">
            <img src="../banner_images/mid-page-banner.png" alt="Featured Banner" class="mid-page-banner-image">
        </a>
    </div>
</section>

<section class="brand-carousel-section">
    <div class="brand-carousel-wrapper">
        <div class="brand-carousel-track">
                <?php foreach ($brands as $brand): 
                    $brand_slug = strtolower(str_replace(' ', '', $brand));
                ?>
                <div class="brand-logo-item" onclick="scrollToBrand('<?php echo $brand_slug; ?>')">
                    <img src="../brand_logos/<?php echo $brand_slug; ?>.png" alt="<?php echo $brand; ?>" onerror="this.style.display='none'; this.parentElement.innerHTML='<h5 style=\'margin:0;color:#333;\'><?php echo $brand; ?></h5>';">
                </div>
                <?php endforeach; ?>
                
                <?php foreach ($brands as $brand): 
                    $brand_slug = strtolower(str_replace(' ', '', $brand));
                ?>
                <div class="brand-logo-item" onclick="scrollToBrand('<?php echo $brand_slug; ?>')">
                    <img src="../brand_logos/<?php echo $brand_slug; ?>.png" alt="<?php echo $brand; ?>" onerror="this.style.display='none'; this.parentElement.innerHTML='<h5 style=\'margin:0;color:#333;\'><?php echo $brand; ?></h5>';">
                </div>
                <?php endforeach; ?>
            </div>
        </div>
</section>

<?php
function renderProductSection($products, $title, $subtitle, $id = '', $showBanner = false, $bannerImage = '') {
    ?>
    <section class="py-5 position-relative" <?php if($id) echo 'id="'.$id.'"'; ?>>
        <?php if ($showBanner && $bannerImage): ?>
            <div class="brand-banner mb-4">
                <img src="<?php echo $bannerImage; ?>" alt="<?php echo $title; ?> Banner" style="width: 100%; height: auto; display: block;">
            </div>
        <?php endif; ?>
        <?php if ($subtitle): ?>
            <div class="section-bg-text"><?php echo $subtitle; ?></div>
        <?php endif; ?>
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-subtitle">PRODUCTS</p>
                <h2 class="section-title"><?php echo $title; ?></h2>
            </div>
            <div class="row">
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="product-card">
                                <?php if ($product['sale']): ?>
                                    <span class="sale-badge">SALE!</span>
                                <?php endif; ?>
                                <img src="../uploads/products/<?php echo $product['image']; ?>"
                                     alt="<?php echo htmlspecialchars($product['name']); ?>"
                                     class="product-image">
                                <div class="product-overlay">
                                    <button class="quick-view-btn" onclick='openQuickView(<?php echo json_encode($product, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                        <i class="fas fa-eye me-2"></i>Quick View
                                    </button>
                                </div>
<h5 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h5>
<?php if (!empty($product['brand'])): 
    $brand_slug = strtolower(str_replace(' ', '', $product['brand']));
?>
    <div class="product-brand">
        <img src="../brand_logos/<?php echo $brand_slug; ?>.png" 
             alt="<?php echo htmlspecialchars($product['brand']); ?>" 
             class="brand-logo-small"
             onerror="this.style.display='none'">
        <span><?php echo htmlspecialchars($product['brand']); ?></span>
    </div>
<?php endif; ?>
<div class="product-rating">
    <?php echo generateStars($product['rating']); ?>
</div>
                                <p class="product-price">
                                    <?php if ($product['original_price']): ?>
                                        <del class="text-muted me-2"><?php echo formatPrice($product['original_price']); ?></del>
                                    <?php endif; ?>
                                    <?php echo formatPrice($product['price']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No products available</h4>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
}


renderProductSection($new_arrivals, 'NEW ARRIVALS', 'PRODUCTS', 'new-arrivals');
?>

<?php
renderProductSection($bestseller, 'BEST SELLER', 'PRODUCTS', 'best-seller');
renderProductSection($basketball_shoes, 'BASKETBALL SHOES', 'PRODUCTS', 'basketball-shoes');
renderProductSection($running_shoes_men, 'RUNNING SHOES', 'PRODUCTS', 'running-shoes-men');
renderProductSection($lifestyle_men, 'LIFESTYLE', 'PRODUCTS', 'lifestyle-men');
renderProductSection($running_shoes_women, 'RUNNING SHOES', 'PRODUCTS', 'running-shoes-women');
renderProductSection($lifestyle_women, 'LIFESTYLE', 'PRODUCTS', 'lifestyle-women');
renderProductSection($kids_products, 'FOR KIDS', 'PRODUCTS', 'kids');
?>

<?php foreach ($brands as $brand):
    $brand_slug = strtolower(str_replace(' ', '', $brand));
    $banner_path = '../banner_images/' . $brand_slug . '-banner.png';
    renderProductSection($brand_products[$brand], $brand, 'PRODUCT', 'brand-' . $brand_slug, true, $banner_path);
endforeach; ?>

<div class="modal fade" id="quickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <img id="modalProductImage" src="" alt="" class="modal-product-image mb-3">
                        <div class="variant-selector-section">
                            <h6 class="mb-2">Select Variant:</h6>
                            <div id="variantThumbnails"></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div id="modalSaleBadge" class="badge bg-danger mb-2" style="display: none;">SALE!</div>
                        <h3 id="modalProductName" class="mb-2"></h3>
                        <p id="modalProductBrand" class="text-muted"></p>
                        <div id="modalProductRating" class="mb-3"></div>
                        <div class="mb-3">
                            <span id="modalOriginalPrice" class="text-decoration-line-through text-muted me-2"></span>
                            <span id="modalProductPrice" class="h4 fw-bold"></span>
                        </div>
                        <div class="mb-3">
                            <span id="modalStockBadge" class="stock-badge">In Stock</span>
                        </div>
                        <p id="modalProductDescription" class="text-muted mb-4"></p>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Size: <span id="selectedSizeDisplay" class="text-muted"></span></label>
                            <div id="sizeOptions"></div>
                            <small class="text-muted" id="sizeHint">Please select a variant first</small>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Quantity:</label>
                            <div class="quantity-selector">
                                <button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                                <input type="number" class="quantity-input" id="quantity" value="1" min="1" max="10">
                                <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                            </div>
                        </div>
                        <div class="d-grid">
                            <button class="btn btn-dark btn-lg" onclick="addToCart()" id="addToCartBtn">
                                <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
let searchActive = false;
let currentProduct = null;
let selectedVariant = null;
let selectedSize = null;
const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
const username = <?php echo $is_logged_in ? json_encode($_SESSION['username']) : 'null'; ?>;

// Page State Management
let currentView = 'home'; // 'home' or 'section'
let currentSectionId = null;

// Show only home view (banner + new arrivals)
function showHomeView() {
    currentView = 'home';
    currentSectionId = null;
    
    // Hide all brand banners
    document.querySelectorAll('.brand-banner').forEach(banner => {
        banner.classList.remove('active');
    });
    
    // Show banner and brand carousel
    document.querySelector('.slider-section').style.display = 'block';
    document.querySelector('.brand-carousel-section').style.display = 'block';
    
    // Show only new arrivals section and mid-page banner
    document.querySelectorAll('section.py-5').forEach(section => {
        if (section.id === 'new-arrivals') {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
    
    // Show mid-page banner
    const midBanner = document.getElementById('mid-page-banner');
    if (midBanner) {
        midBanner.style.display = 'block';
    }
    
    // Hide search results if any
    const searchSection = document.getElementById('searchResults');
    if (searchSection) {
        searchSection.style.display = 'none';
    }
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Show only specific section view
function showSectionView(sectionId) {
    currentView = 'section';
    currentSectionId = sectionId;
    
    // Hide main banner and brand carousel
    document.querySelector('.slider-section').style.display = 'none';
    document.querySelector('.brand-carousel-section').style.display = 'none';
    
    // Hide mid-page banner
    const midBanner = document.getElementById('mid-page-banner');
    if (midBanner) {
        midBanner.style.display = 'none';
    }
    
    // Hide all brand banners first
    document.querySelectorAll('.brand-banner').forEach(banner => {
        banner.classList.remove('active');
    });
    
    // Show brand banner if it's a brand section
    if (sectionId.startsWith('brand-')) {
        const brandSlug = sectionId.replace('brand-', '');
        const brandBanner = document.getElementById(brandSlug + 'Banner');
        if (brandBanner) {
            brandBanner.classList.add('active');
        }
    }
    
    // Hide all sections except the target
    document.querySelectorAll('section.py-5').forEach(section => {
        if (section.id === sectionId) {
            section.style.display = 'block';
        } else {
            section.style.display = 'none';
        }
    });
    
    // Hide search results if any
    const searchSection = document.getElementById('searchResults');
    if (searchSection) {
        searchSection.style.display = 'none';
    }
    
    // Scroll to top
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Toggle Search
function toggleSearch() {
    const searchInput = document.getElementById('searchInput');
    searchActive = !searchActive;
   
    if (searchActive) {
        searchInput.classList.add('active');
        setTimeout(() => searchInput.focus(), 300);
    } else {
        searchInput.classList.remove('active');
        searchInput.value = '';
    }
}

// Handle Search on Enter Key
function handleSearchEnter(event) {
    if (event.key === 'Enter') {
        performSearch();
    }
}

// Toggle Profile Menu
function toggleProfile() {
    if (!isLoggedIn) {
        openLoginModal();
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
   
    if (!event.target.closest('.search-container')) {
        const searchInput = document.getElementById('searchInput');
        searchInput.classList.remove('active');
        searchActive = false;
    }
});

// Mobile Menu Functions
function toggleMobileMenu() {
    const panel = document.getElementById('mobileMenuPanel');
    const overlay = document.getElementById('mobileMenuOverlay');
    const auth = document.getElementById('mobileMenuAuth');
   
    panel.classList.toggle('active');
    overlay.classList.toggle('active');
    if (auth) {
        auth.classList.toggle('active');
    }
}

function closeMobileMenu() {
    const panel = document.getElementById('mobileMenuPanel');
    const overlay = document.getElementById('mobileMenuOverlay');
    const auth = document.getElementById('mobileMenuAuth');
   
    panel.classList.remove('active');
    overlay.classList.remove('active');
    if (auth) {
        auth.classList.remove('active');
    }
}

function toggleSubmenu(id) {
    const submenu = document.getElementById(id);
    submenu.classList.toggle('active');
}

// Login Modal Functions
function openLoginModal() {
    const modal = document.getElementById('loginModal');
    modal.classList.add('active');
    document.getElementById('loginFormContainer').classList.remove('hidden');
    document.getElementById('signupFormContainer').classList.add('hidden');
}

function openSignupModal() {
    const modal = document.getElementById('loginModal');
    modal.classList.add('active');
    document.getElementById('loginFormContainer').classList.add('hidden');
    document.getElementById('signupFormContainer').classList.remove('hidden');
}

function closeLoginModal() {
    const modal = document.getElementById('loginModal');
    modal.classList.remove('active');
}

function switchToSignup() {
    document.getElementById('loginFormContainer').classList.add('hidden');
    document.getElementById('signupFormContainer').classList.remove('hidden');
}

function switchToLogin() {
    document.getElementById('signupFormContainer').classList.add('hidden');
    document.getElementById('loginFormContainer').classList.remove('hidden');
}

// Scroll to Brand Section
function scrollToBrand(brandSlug) {
    showSectionView('brand-' + brandSlug);
}

// Perform Search
function performSearch() {
    const searchInput = document.getElementById('searchInput');
    const searchInputMobile = document.getElementById('searchInputMobile');
    const searchTerm = (searchInput.value || searchInputMobile.value).trim().toLowerCase();
   
    if (!searchTerm) {
        alert('Please enter a search term');
        return;
    }
   
    currentView = 'search';
    
    // Hide banner and brand carousel
    document.querySelector('.slider-section').style.display = 'none';
    document.querySelector('.brand-carousel-section').style.display = 'none';
   
    // Hide all sections
    document.querySelectorAll('section.py-5').forEach(section => {
        section.style.display = 'none';
    });
   
    // Create or show search results section
    let searchSection = document.getElementById('searchResults');
    if (!searchSection) {
        searchSection = document.createElement('section');
        searchSection.id = 'searchResults';
        searchSection.className = 'py-5';
        document.querySelector('.slider-section').after(searchSection);
    }
   
    searchSection.style.display = 'block';
    searchSection.innerHTML = `
        <div class="container">
            <div class="text-center mb-5">
                <p class="section-subtitle">SEARCH RESULTS</p>
                <h2 class="section-title">Results for "${searchTerm}"</h2>
                <button class="btn btn-dark mt-3" onclick="showHomeView()">
                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                </button>
            </div>
            <div class="row" id="searchResultsContainer"></div>
        </div>
    `;
   
    // Search in all product sections
    const resultsContainer = document.getElementById('searchResultsContainer');
    const allSections = document.querySelectorAll('section.py-5[id]');
    const uniqueProducts = new Map();
   
    allSections.forEach(section => {
        section.querySelectorAll('.product-card').forEach(card => {
            const title = card.querySelector('.product-title')?.textContent.toLowerCase();
            const productName = card.querySelector('.product-title')?.textContent;
           
            if (title && title.includes(searchTerm) && !uniqueProducts.has(productName)) {
                const clone = card.closest('.col-lg-3').cloneNode(true);
                resultsContainer.appendChild(clone);
                uniqueProducts.set(productName, true);
            }
        });
    });
   
    if (uniqueProducts.size === 0) {
        resultsContainer.innerHTML = `
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4>No products found for "${searchTerm}"</h4>
                <p class="text-muted">Try searching with different keywords</p>
                <button class="btn btn-dark mt-3" onclick="showHomeView()">
                    <i class="fas fa-arrow-left me-2"></i>Back to Home
                </button>
            </div>
        `;
    }
   
    searchSection.scrollIntoView({ behavior: 'smooth' });
    searchInput.value = '';
    searchInputMobile.value = '';
    searchActive = false;
    searchInput.classList.remove('active');
}

// Clear Search (now redirects to home)
function clearSearch() {
    showHomeView();
}

// Open Quick View Modal
function openQuickView(product) {
    if (!isLoggedIn) {
        openLoginModal();
        return;
    }
   
    currentProduct = product;
    selectedVariant = null;
    selectedSize = null;

    document.getElementById('modalProductName').textContent = product.name;
    document.getElementById('modalProductBrand').textContent = product.brand || '';
    document.getElementById('modalProductPrice').textContent = formatPrice(product.price);
    document.getElementById('modalSaleBadge').style.display = product.sale ? 'inline-block' : 'none';

    const originalPriceEl = document.getElementById('modalOriginalPrice');
    if (product.original_price) {
        originalPriceEl.textContent = formatPrice(product.original_price);
        originalPriceEl.style.display = 'inline';
    } else {
        originalPriceEl.style.display = 'none';
    }

    const description = product.description || 'Premium quality sneakers designed for comfort and style.';
    document.getElementById('modalProductDescription').textContent = description;
    document.getElementById('modalProductRating').innerHTML = generateStars(product.rating);

    renderVariantThumbnails(product.variants);

    if (product.variants && product.variants.length > 0) {
        selectVariant(0);
    }

    document.getElementById('quantity').value = 1;
    const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
    modal.show();
}

// Render Variant Thumbnails
function renderVariantThumbnails(variants) {
    const container = document.getElementById('variantThumbnails');
    container.innerHTML = '';

    if (!variants || variants.length === 0) {
        container.innerHTML = '<p class="text-muted small">No variants available</p>';
        return;
    }

    variants.forEach((variant, index) => {
        const img = document.createElement('img');
        img.src = '../uploads/products/' + variant.image;
        img.className = 'variant-thumbnail';
        img.alt = 'Variant ' + (index + 1);
        img.onclick = () => selectVariant(index);
        container.appendChild(img);
    });
}

// Select Variant
function selectVariant(index) {
    const variant = currentProduct.variants[index];
    selectedVariant = variant;
    selectedSize = null;

    document.getElementById('modalProductImage').src = '../uploads/products/' + variant.image;

    document.querySelectorAll('.variant-thumbnail').forEach((thumb, i) => {
        if (i === index) {
            thumb.classList.add('active');
        } else {
            thumb.classList.remove('active');
        }
    });

    updateStockBadge(variant.stock);
    renderSizes(variant.size, variant.stock);

    const maxQty = variant.stock > 0 ? Math.min(variant.stock, 10) : 1;
    document.getElementById('quantity').max = maxQty;
    document.getElementById('quantity').value = 1;
    document.getElementById('sizeHint').textContent = 'Available sizes for this variant';
}

// Update Stock Badge
function updateStockBadge(stock) {
    const stockBadge = document.getElementById('modalStockBadge');
    if (stock <= 0) {
        stockBadge.textContent = 'Out of Stock';
        stockBadge.className = 'stock-badge out';
    } else if (stock <= 5) {
        stockBadge.textContent = 'Only ' + stock + ' left!';
        stockBadge.className = 'stock-badge low';
    } else {
        stockBadge.textContent = 'In Stock (' + stock + ')';
        stockBadge.className = 'stock-badge';
    }
}

// Render Sizes
function renderSizes(sizeString, stock) {
    const sizeOptions = document.getElementById('sizeOptions');
    sizeOptions.innerHTML = '';

    if (!sizeString || sizeString.trim() === '') {
        sizeOptions.innerHTML = '<p class="text-muted small">No sizes available</p>';
        return;
    }

    const sizes = sizeString.split(',').map(s => s.trim());
    sizes.forEach(size => {
        const sizeBtn = document.createElement('span');
        sizeBtn.className = 'size-option';
        if (stock <= 0) {
            sizeBtn.classList.add('disabled');
        }
        sizeBtn.textContent = size;
        sizeBtn.onclick = function() {
            if (stock > 0) {
                selectSize(this, size);
            }
        };
        sizeOptions.appendChild(sizeBtn);
    });
}

// Select Size
function selectSize(element, size) {
    if (!selectedVariant) {
        alert('Please select a variant first');
        return;
    }

    if (selectedVariant.stock <= 0) {
        alert('This variant is out of stock');
        return;
    }
   
    document.querySelectorAll('.size-option').forEach(el => el.classList.remove('selected'));
    element.classList.add('selected');
    selectedSize = size;
    document.getElementById('selectedSizeDisplay').textContent = '(' + size + ' selected)';
}

// Change Quantity
function changeQuantity(delta) {
    const input = document.getElementById('quantity');
    let value = parseInt(input.value) + delta;
    const max = parseInt(input.max);
    if (value < 1) value = 1;
    if (value > max) value = max;
    input.value = value;
}

// Add to Cart
function addToCart() {
    if (!isLoggedIn) {
        bootstrap.Modal.getInstance(document.getElementById('quickViewModal')).hide();
        openLoginModal();
        return;
    }
   
    if (!selectedVariant) {
        alert('Please select a variant');
        return;
    }
    if (!selectedSize) {
        alert('Please select a size');
        return;
    }
    if (selectedVariant.stock <= 0) {
        alert('This variant is out of stock');
        return;
    }

    const quantity = document.getElementById('quantity').value;
    const addToCartBtn = document.getElementById('addToCartBtn');
    addToCartBtn.disabled = true;
    addToCartBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';
   
    const cartData = {
        product_id: currentProduct.id,
        product_name: currentProduct.name,
        product_price: currentProduct.price,
        variant_id: selectedVariant.id,
        variant_image: selectedVariant.image,
        size: selectedSize,
        quantity: parseInt(quantity)
    };
   
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(cartData)
    })
    .then(response => response.json())
    .then(data => {
        addToCartBtn.disabled = false;
        addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Add to Cart';
       
        if (data.success) {
            alert('✓ Product added to cart!\n\nProduct: ' + currentProduct.name + '\nSize: ' + selectedSize + '\nQuantity: ' + quantity);
            updateCartBadge();
            bootstrap.Modal.getInstance(document.getElementById('quickViewModal')).hide();
        } else {
            alert('Error: ' + (data.message || 'Failed to add to cart'));
        }
    })
    .catch(error => {
        addToCartBtn.disabled = false;
        addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart me-2"></i>Add to Cart';
        console.error('Error:', error);
        alert('Failed to add to cart. Please try again.');
    });
}

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

// Format Price
function formatPrice(price) {
    return '₱' + parseFloat(price).toLocaleString('en-PH', {minimumFractionDigits: 2});
}

// Generate Stars
function generateStars(rating) {
    let stars = '';
    for (let i = 1; i <= 5; i++) {
        if (i <= rating) {
            stars += '<i class="fas fa-star text-warning"></i>';
        } else {
            stars += '<i class="far fa-star text-warning"></i>';
        }
    }
    return stars;
}

// Image Click to Cycle Variants
document.addEventListener('DOMContentLoaded', function() {
    const modalImage = document.getElementById('modalProductImage');
    if (modalImage) {
        modalImage.addEventListener('click', function() {
            if (!currentProduct || !currentProduct.variants) return;
            const currentIndex = currentProduct.variants.findIndex(v => v.id === selectedVariant.id);
            const nextIndex = (currentIndex + 1) % currentProduct.variants.length;
            selectVariant(nextIndex);
        });
    }
   
// Handle navbar clicks to show sections
document.querySelectorAll('.nav-link:not([href="about.php"]):not(.dropdown-toggle), .dropdown-item').forEach(link => {
    link.addEventListener('click', function(e) {
        const href = this.getAttribute('href');
        if (href && href.startsWith('#')) {
            e.preventDefault();
            const targetId = href.substring(1);
            showSectionView(targetId);
            
            // Close any open dropdowns
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                menu.classList.remove('show');
            });
            
            // Close mobile menu if open
            const offcanvas = bootstrap.Offcanvas.getInstance(document.getElementById('mobileMenu'));
            if (offcanvas) {
                offcanvas.hide();
            }
        }
    });
});
    // Prevent dropdown toggle links from doing anything
document.querySelectorAll('#menDropdown, #womenDropdown, #brandsDropdown').forEach(dropdownToggle => {
    dropdownToggle.addEventListener('click', function(e) {
        e.preventDefault();
        // Don't call stopPropagation - let Bootstrap handle the dropdown
    });
});
    
    // Make logo clickable to go home
    document.querySelector('.navbar-brand').addEventListener('click', function(e) {
        e.preventDefault();
        showHomeView();
    });
    
    // Check if there's a hash in the URL on page load
    if (window.location.hash) {
        const hash = window.location.hash.substring(1); // Remove the # symbol
        if (hash === 'home') {
            showHomeView();
        } else {
            showSectionView(hash);
        }
    } else {
        // Initialize home view only if no hash
        showHomeView();
    }
    
    // Handle hash changes (when clicking back/forward browser buttons)
    window.addEventListener('hashchange', function() {
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            if (hash === 'home') {
                showHomeView();
            } else {
                showSectionView(hash);
            }
        } else {
            showHomeView();
        }
    });
   
// Update cart badge on page load
    if (isLoggedIn) {
        updateCartBadge();
    }
    
    // Back to Top Button functionality
    const backToTopBtn = document.getElementById('backToTopBtn');
    
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopBtn.classList.add('show');
        } else {
            backToTopBtn.classList.remove('show');
        }
    });
});

// Scroll to Top function
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// NEW: Function to explicitly OPEN the chat
// NEW: Toggle function (Replaces openChat)
function toggleChat() {
    console.log('Toggling chat window');
    const chatWindow = document.getElementById('chatWindow');
    const chatBubbleBtn = document.getElementById('chatBubbleBtn');
    
    // Check if currently active
    if (chatWindow.classList.contains('active')) {
        // If active, close it
        chatWindow.classList.remove('active');
        chatBubbleBtn.classList.remove('active');
    } else {
        // If not active, open it
        chatWindow.classList.add('active');
        chatBubbleBtn.classList.add('active');
        
        // Fetch chat messages when opening
        if (isLoggedIn && username) {
            const chats = fetchChatMessages();
            console.log(chats);
        }
    }
}

// Fetch chat messages from database
function fetchChatMessages() {
    fetch('../chatbot/api.php?action=get_chat_messages&username=' + encodeURIComponent(username))
        .then(response => response.json())
        .then(data => {
            console.log('Chat messages:', data);
            if (data.success && data.messages) {
                // Clear existing messages (except the greeting)
                const chatMessages = document.getElementById('chatMessages');
                const existingMessages = chatMessages.querySelectorAll('.message:not(.bot)');
                existingMessages.forEach(msg => msg.remove());
                
                // Add fetched messages in order (oldest to newest)
                data.messages.forEach(msg => {
                    addMessage(msg.chat, 'user');
                });
            }
        })
        .catch(error => {
            console.error('Error fetching chat messages:', error);
        });
}

function closeChat() {
    const chatWindow = document.getElementById('chatWindow');
    const chatBubbleBtn = document.getElementById('chatBubbleBtn');
    
    chatWindow.classList.remove('active');
    chatBubbleBtn.classList.remove('active');
}

  // Go Back to Menu (Preserving chat history)
  function goBackToMenu() {
    const chatBackBtn = document.getElementById('chatBackBtn');
    const chatHeaderTitle = document.getElementById('chatHeaderTitle');
    const quickRepliesContainer = document.getElementById('quickRepliesContainer');
    const chatMessages = document.getElementById('chatMessages');

    // Show the quick reply container
    quickRepliesContainer.style.display = 'block';

    // Hide the back button and reset header
    chatBackBtn.style.display = 'none';
    chatHeaderTitle.textContent = 'LEE Sneakers Support';

    // Scroll to the top (to show the initial greeting)
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

    // Hide quick replies container
    const container = document.getElementById('quickRepliesContainer');
    container.style.display = 'none';

    // Show back button
    const chatBackBtn = document.getElementById('chatBackBtn');
    const chatHeaderTitle = document.getElementById('chatHeaderTitle');
    chatBackBtn.style.display = 'block';
    chatHeaderTitle.textContent = 'LEE Sneakers Support';

    // Add bot response
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

    // console.log('User message:', message);
    // console.log(username);

    if (!message) return;

    fetch('../chatbot/api.php?action=save_message', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
        username: username,
        chat: message
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Message saved:', data);
    })
    .catch(error => {
        console.error('Error saving message:', error);
    });

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
      <button class="chat-close-btn" onclick="closeChat()">
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

    <div id="quickRepliesContainer" style="display: block;">
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

<?php require_once '../components/footer.php'; ?>

<button class="back-to-top" id="backToTopBtn" onclick="scrollToTop()">
    <i class="fas fa-chevron-up"></i>
</button>
</body>
</html>