<?php
require_once '../config/database.php';

if (!isLoggedIn()) {
    redirect('../index.php');
}

// Redirect admins to admin panel
if (isAdmin()) {
    redirect('../admin/index.php');
}

$user_id = $_SESSION['user_id'];
// ... rest of code

// Get cart items
$cart_query = "SELECT 
                c.id as cart_id,
                c.quantity,
                c.size,
                c.price,
                p.id as product_id,
                p.name as product_name,
                pv.image,
                pv.stock
               FROM cart c
               JOIN products p ON c.product_id = p.id
               JOIN product_variants pv ON c.variant_id = pv.id
               WHERE c.user_id = ?";

$stmt = $conn->prepare($cart_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result();
$stmt->close();

// Check if cart is empty
if ($cart_items->num_rows === 0) {
    $_SESSION['error'] = 'Your cart is empty!';
    redirect('cart.php');
}

// Calculate total
$subtotal = 0;
$items = [];
while ($item = $cart_items->fetch_assoc()) {
    $item_total = $item['price'] * $item['quantity'];
    $subtotal += $item_total;
    $items[] = $item;
}

// Get user details
$user_query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// helper for displaying price if formatPrice exists in your codebase, otherwise define it
if (!function_exists('formatPrice')) {
    function formatPrice($amount) {
        return '₱' . number_format((float)$amount, 2);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - LEE Sneakers</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Keep your visual style, but ensure Order Summary won't overlap navbar */
        .checkout-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }
        .order-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .order-item:last-child {
            border-bottom: none;
        }
        .order-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
        }

        /* Ensure sticky summary doesn't overlap with the navbar */
        .summary-sticky {
            position: sticky;
            top: 100px; /* keeps it below navbar */
            z-index: 10;
        }

        /* GCash QR and upload styling */
        #gcash-qr {
            max-width: 240px;
            display: none;
            margin-top: 10px;
            border: 1px solid #eee;
            padding: 8px;
            border-radius: 6px;
            background: #fff;
        }
        #gcash-upload-container {
            display: none;
            margin-top: 10px;
        }

        /* Small responsiveness tweak in case navbar height changes */
        @media (max-width: 991px) {
            .summary-sticky { top: 80px; }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">LEE</a>
            <div class="ms-auto">
                <span class="nav-link">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="mb-4" style="color: #FEC700;">Checkout</h2>

        <!-- NOTE: enctype required to upload GCash screenshot -->
        <form action="process_payment.php" method="POST" id="checkoutForm" enctype="multipart/form-data">
            <div class="row">
                <div class="col-lg-7">
                    <!-- Customer Information -->
                    <div class="checkout-section">
                        <h4 class="mb-4"><i class="fas fa-user me-2"></i>Customer Information</h4>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name *</label>
                                <input type="text" class="form-control" name="full_name" 
                                       value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" 
                                       value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone Number *</label>
                            <input type="text" class="form-control" name="phone" 
                                   placeholder="+63 XXX XXX XXXX" required>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="checkout-section">
                        <h4 class="mb-4"><i class="fas fa-map-marker-alt me-2"></i>Shipping Address</h4>
                        <div class="mb-3">
                            <label class="form-label">Street Address *</label>
                            <input type="text" class="form-control" name="street" 
                                   placeholder="House/Unit No., Street Name" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Barangay *</label>
                                <input type="text" class="form-control" name="barangay" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">City/Municipality *</label>
                                <!-- IMPORTANT: id="city" used by JS live detection -->
                                <input type="text" class="form-control" name="city" id="city" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Province *</label>
                                <input type="text" class="form-control" name="province" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Postal Code *</label>
                                <input type="text" class="form-control" name="postal_code" 
                                       placeholder="XXXX" required>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method -->
                    <div class="checkout-section">
                        <h4 class="mb-4"><i class="fas fa-credit-card me-2"></i>Payment Method</h4>

                        <!-- Radio buttons -->
                        <div class="mb-3" id="payment-options">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_radio" id="payment-gcash" value="GCash" checked>
                                <label class="form-check-label" for="payment-gcash">
                                    GCash (Nationwide) — upload proof after payment
                                </label>
                            </div>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="radio" name="payment_radio" id="payment-cod" value="COD">
                                <label class="form-check-label" for="payment-cod">
                                    Cash on Delivery (COD) — available within Metro Manila only
                                </label>
                            </div>
                        </div>

                        <!-- GCash QR + Upload -->
                        <div id="gcash-info">
                            <img id="gcash-qr" src="../uploads/qr/code.jpg" alt="GCash QR for LEE Sneakers">
                            <div id="gcash-upload-container">
                                <label for="gcash_screenshot" class="form-label mt-2">Upload GCash Payment Screenshot *</label>
                                <input type="file" class="form-control" name="gcash_screenshot" id="gcash_screenshot" accept="image/*">
                                <small class="text-muted">Please upload a screenshot of your GCash payment. This is required for order confirmation.</small>
                            </div>
                        </div>

                        <!-- Hidden inputs that will be submitted to server -->
                        <input type="hidden" name="payment_method" id="payment_method" value="GCash">
                        <input type="hidden" name="shipping_fee" id="shipping_fee_input" value="0">
                    </div>
                </div>

                <div class="col-lg-5">
                    <!-- Order Summary -->
                    <div class="checkout-section summary-sticky">
                        <h4 class="mb-4">Order Summary</h4>
                        
                        <?php foreach ($items as $item): ?>
                            <div class="order-item">
                                <div class="d-flex">
                                    <img src="../uploads/products/<?php echo htmlspecialchars($item['image']); ?>" alt="">
                                    <div class="ms-3 flex-grow-1">
                                        <h6 class="mb-1"><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                        <small class="text-muted">Size: <?php echo htmlspecialchars($item['size']); ?></small>
                                        <div class="d-flex justify-content-between mt-2">
                                            <span>Qty: <?php echo (int)$item['quantity']; ?></span>
                                            <strong><?php echo formatPrice($item['price'] * $item['quantity']); ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <hr class="my-3">

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <!-- subtotal displayed with two decimals -->
                            <strong id="subtotal_display"><?php echo formatPrice($subtotal); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping:</span>
                            <strong id="shipping_display" class="text-success">₱0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Payment Method:</span>
                            <strong id="payment_display">GCash</strong>
                        </div>
                        
                        <hr class="my-3">
                        
                        <div class="d-flex justify-content-between mb-4">
                            <h5>Total:</h5>
                            <h5 class="text-primary" id="total_display"><?php echo formatPrice($subtotal); ?></h5>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-dark btn-lg" id="placeOrderBtn">
                                <i class="fas fa-check-circle me-2"></i>Place Order
                            </button>
                            <a href="cart.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to Cart
                            </a>
                        </div>

                        <div class="alert alert-warning mt-3 mb-0">
                            <small>
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Your order will be confirmed by our team before processing.
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        (function() {
            // Metro Manila city list (kept per your list)
            const metroManilaCities = [
                "Makati", "Quezon City", "Manila", "Pasig", "Taguig",
                "Mandaluyong", "San Juan", "Marikina", "Pasay", "Parañaque",
                "Las Piñas", "Muntinlupa", "Malabon", "Navotas", "Valenzuela", "Caloocan"
            ].map(c => c.toLowerCase());

            // DOM refs
            const cityInput = document.getElementById('city');
            const paymentGcashRadio = document.getElementById('payment-gcash');
            const paymentCodRadio = document.getElementById('payment-cod');
            const gcashQr = document.getElementById('gcash-qr');
            const gcashUploadContainer = document.getElementById('gcash-upload-container');
            const gcashFileInput = document.getElementById('gcash_screenshot');

            const shippingDisplay = document.getElementById('shipping_display');
            const subtotalDisplay = document.getElementById('subtotal_display');
            const totalDisplay = document.getElementById('total_display');
            const paymentDisplay = document.getElementById('payment_display');

            const paymentMethodHidden = document.getElementById('payment_method');
            const shippingFeeHidden = document.getElementById('shipping_fee_input');

            // parse subtotal from PHP-rendered text (e.g., ₱1,234.00)
            const subtotalAmount = <?php echo json_encode((float)$subtotal); ?>;

            // Helper: format to PHP-style currency with two decimals
            function formatPHP(amount) {
                return '₱' + Number(amount).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Detect if city is Metro Manila
            function isCityMetro(cityVal) {
                if (!cityVal) return false;
                const lower = cityVal.trim().toLowerCase();
                // match if input contains a known city name (partial allowed), or equals
                return metroManilaCities.some(m => lower.includes(m));
            }

            // Update shipping/payment UI based on city
            function updateByCity() {
                const cityVal = cityInput.value || '';
                const metro = isCityMetro(cityVal);

                if (metro) {
                    // Metro Manila: free shipping, enable COD
                    shippingFeeHidden.value = '0';
                    shippingDisplay.textContent = formatPHP(0);
                    paymentCodRadio.disabled = false;

                    // keep currently selected payment if valid
                    // if COD was previously disabled and gcash selected, allow COD to remain unselected unless user chooses
                } else {
                    // Outside Metro Manila: shipping 100, COD disabled and unselected, force GCash
                    shippingFeeHidden.value = '100';
                    shippingDisplay.textContent = formatPHP(100);
                    paymentCodRadio.checked = false;
                    paymentCodRadio.disabled = true;

                    // force GCash selection
                    paymentGcashRadio.checked = true;
                }

                // Update total & payment display
                updateTotalsAndPaymentDisplay();
            }

            function updateTotalsAndPaymentDisplay() {
                const shipping = parseFloat(shippingFeeHidden.value) || 0;
                const total = parseFloat(subtotalAmount) + shipping;
                subtotalDisplay.textContent = formatPHP(subtotalAmount);
                shippingDisplay.textContent = formatPHP(shipping);
                totalDisplay.textContent = formatPHP(total);

                const method = paymentGcashRadio.checked ? 'GCash' : (paymentCodRadio.checked ? 'Cash on Delivery' : 'GCash');
                paymentDisplay.textContent = method;

                // hidden input value
                paymentMethodHidden.value = (method === 'Cash on Delivery') ? 'COD' : 'GCash';

                // show/hide gcash UI
                if (paymentGcashRadio.checked) {
                    gcashQr.style.display = 'block';
                    gcashUploadContainer.style.display = 'block';
                } else {
                    gcashQr.style.display = 'none';
                    gcashUploadContainer.style.display = 'none';
                }
            }

            // Listeners
            cityInput.addEventListener('input', function() {
                updateByCity();
            }, { passive: true });

            paymentGcashRadio.addEventListener('change', updateTotalsAndPaymentDisplay);
            paymentCodRadio.addEventListener('change', updateTotalsAndPaymentDisplay);

            // initial UI setup
            (function init() {
                // set initial hidden shipping fee (default 0)
                shippingFeeHidden.value = '0';
                // show GCash by default
                paymentGcashRadio.checked = true;
                updateTotalsAndPaymentDisplay();
            })();

            // Form validation before submit
            const checkoutForm = document.getElementById('checkoutForm');
            const placeBtn = document.getElementById('placeOrderBtn');

            checkoutForm.addEventListener('submit', function(e) {
                // Disable button to prevent double submit (we'll re-enable after checks)
                placeBtn.disabled = true;
                placeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

                // Get current city validity
                const cityVal = cityInput.value || '';
                const metro = isCityMetro(cityVal);

                // If COD selected but city is not in Metro Manila -> block
                if (paymentCodRadio.checked && !metro) {
                    e.preventDefault();
                    alert('Cash on Delivery (COD) is only available within Metro Manila. Please choose GCash or update your city.');
                    placeBtn.disabled = false;
                    placeBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Place Order';
                    return false;
                }

                // If GCash selected -> require screenshot upload (only for outside Metro Manila or always per your requirement)
                if (paymentGcashRadio.checked) {
                    // require file input to be present
                    if (!gcashFileInput || !gcashFileInput.files || gcashFileInput.files.length === 0) {
                        e.preventDefault();
                        alert('Please upload your GCash payment screenshot before placing the order.');
                        placeBtn.disabled = false;
                        placeBtn.innerHTML = '<i class="fas fa-check-circle me-2"></i>Place Order';
                        return false;
                    }
                }

                // All client-side checks passed; allow the form to submit
                // NOTE: server-side must re-validate these conditions in process_payment.php
                return true;
            });
        })();
    </script>
</body>
</html>