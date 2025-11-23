<?php
// 1. AUTOMATIC PATH DETECTION LOGIC
$current_script = $_SERVER['SCRIPT_NAME']; // Kunin ang current file path

// Default paths
$shop_path = "./";
$legal_path = "../Legal/";

// Check kung nasaan folder tayo
if (strpos($current_script, '/user/') !== false) {
    // Nasa USER folder tayo (index.php, about.php, etc.)
    $shop_path = "./";
    $legal_path = "../Legal/";
} elseif (strpos($current_script, '/Legal/') !== false) {
    // Nasa LEGAL folder tayo (privacy_policy.php, etc.)
    $shop_path = "../user/";
    $legal_path = "./"; // Nasa loob na tayo ng Legal, kaya no need lumabas
}

// Check if on Homepage for Smooth Scroll Logic
$is_on_homepage = (basename($current_script) == 'index.php' && strpos($current_script, '/user/') !== false);

// 2. Ensure database connection exists
if (!isset($conn)) {
    // Tantiya kung nasaan ang config base sa current folder
    $config_path = (strpos($current_script, '/Legal/') !== false) ? '../config/database.php' : '../config/database.php';
    if (file_exists(__DIR__ . '/../../config/database.php')) {
        require_once __DIR__ . '/../../config/database.php';
    } elseif (file_exists($config_path)) {
        require_once $config_path;
    }
}

// 3. Ensure brands data exists
if (!isset($brands) && isset($conn)) {
    $footer_brands_query = "SELECT DISTINCT name FROM brands ORDER BY name ASC";
    $footer_brands_result = $conn->query($footer_brands_query);
    $brands = [];
    while ($brand_row = $footer_brands_result->fetch_assoc()) {
        $brands[] = $brand_row['name'];
    }
}
?>

<style>
    /* ===== FOOTER SECTION STYLES ===== */
    .footer-section {
        background: #064734;
        color: #ffffff;
        padding: 60px 0 25px;
        margin-top: 80px;
        font-family: 'Poppins', sans-serif;
    }

    .footer-title {
        color: #FEC700;
        font-weight: 700;
        font-size: 1rem;
        margin-bottom: 18px;
        letter-spacing: 1px;
        text-transform: uppercase;
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
        .footer-section { padding: 40px 0 20px; margin-top: 50px; }
        .footer-title, .footer-text, .footer-links { text-align: center; }
        .footer-text { margin: 0 auto 20px; }
        .social-icons { justify-content: center; }
        .footer-bottom { font-size: 0.8rem; }
    }
</style>

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
                    <?php 
                    if(isset($brands) && is_array($brands)): 
                        foreach ($brands as $brand): 
                            $brand_slug = strtolower(str_replace(' ', '', $brand));
                            
                            // Build Link: Use full path if not on homepage
                            $href = ($is_on_homepage) ? "#brand-" . $brand_slug : $shop_path . "index.php#brand-" . $brand_slug;
                            
                            // JS only on homepage
                            $onclick = ($is_on_homepage) ? "onclick=\"if(typeof showSectionView === 'function') { event.preventDefault(); showSectionView('brand-$brand_slug'); }\"" : "";
                    ?>
                        <li>
                            <a href="<?php echo $href; ?>" <?php echo $onclick; ?>>
                                <?php echo htmlspecialchars($brand); ?>
                            </a>
                        </li>
                    <?php 
                        endforeach; 
                    endif; 
                    ?>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="footer-title">SHOP</h5>
                <ul class="footer-links">
                    <?php
                    $shop_links = [
                        'new-arrivals' => 'New Arrivals',
                        'best-seller' => 'Best Seller',
                        'basketball-shoes' => 'Basketball Shoes',
                        'running-shoes-men' => 'Running Shoes Men',
                        'lifestyle-men' => 'Lifestyle Men',
                        'running-shoes-women' => 'Running Shoes Women',
                        'lifestyle-women' => 'Lifestyle Women',
                        'kids' => 'Kids'
                    ];
                    
                    foreach($shop_links as $slug => $label):
                        // Build Link
                        $href = ($is_on_homepage) ? "#" . $slug : $shop_path . "index.php#" . $slug;
                        $onclick = ($is_on_homepage) ? "onclick=\"if(typeof showSectionView === 'function') { event.preventDefault(); showSectionView('$slug'); }\"" : "";
                    ?>
                    <li>
                        <a href="<?php echo $href; ?>" <?php echo $onclick; ?>><?php echo $label; ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-lg-2 col-md-6 mb-4">
                <h5 class="footer-title">LEGAL</h5>
                <ul class="footer-links">
                    <li><a href="<?php echo $legal_path; ?>privacy_policy.php">Privacy Policy</a></li>
                    <li><a href="<?php echo $legal_path; ?>refund_policy.php">Refund Policy</a></li>
                    <li><a href="<?php echo $legal_path; ?>shipping_policy.php">Shipping Policy</a></li>
                    <li><a href="<?php echo $legal_path; ?>terms_of_service.php">Terms of Service</a></li>
                    <li><a href="<?php echo $legal_path; ?>payment_options.php">Payment Options</a></li>
                </ul>
            </div>

            <div class="col-lg-3 col-md-6 mb-4">
                <h5 class="footer-title">EXPLORE</h5>
                <ul class="footer-links">
                    <li><a href="<?php echo $shop_path; ?>about.php">About Us</a></li>
                    <li><a href="<?php echo $shop_path; ?>index.php">Shop</a></li>
                    <li><a href="<?php echo $shop_path; ?>my_orders.php">My Orders</a></li>
                    <li><a href="<?php echo $shop_path; ?>profile.php">My Profile</a></li>
                    <li><a href="<?php echo $shop_path; ?>cart.php">Shopping Cart</a></li>
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