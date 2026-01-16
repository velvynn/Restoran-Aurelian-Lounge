<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data user
$db->query('SELECT * FROM users WHERE id = :id');
$db->bind(':id', $user_id);
$user = $db->single();

// Ambil data cart
$db->query('SELECT c.*, p.name, p.price, p.image, p.stock 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = :user_id 
            ORDER BY c.created_at DESC');
$db->bind(':user_id', $user_id);
$cart_items = $db->resultSet();

// Jika cart kosong, redirect ke cart
if (empty($cart_items)) {
    $_SESSION['error'] = 'Your cart is empty. Please add items before checkout.';
    header('Location: cart.php');
    exit();
}

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax = $subtotal * 0.10; // 10% tax
$shipping = $subtotal > 0 ? 15000 : 0; // Shipping fee
$total = $subtotal + $tax + $shipping;

// Handle checkout submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_checkout'])) {
    $payment_method = sanitize($_POST['payment_method']);
    $delivery_address = sanitize($_POST['delivery_address'] ?? $user['address']);
    $notes = sanitize($_POST['notes'] ?? '');
    
    // Generate order code
    $order_code = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Create order
        $db->query('INSERT INTO orders (user_id, order_code, total_amount, payment_method, delivery_address, notes) 
                   VALUES (:user_id, :order_code, :total_amount, :payment_method, :delivery_address, :notes)');
        $db->bind(':user_id', $user_id);
        $db->bind(':order_code', $order_code);
        $db->bind(':total_amount', $subtotal);
        $db->bind(':payment_method', $payment_method);
        $db->bind(':delivery_address', $delivery_address);
        $db->bind(':notes', $notes);
        $db->execute();
        
        $order_id = $db->lastInsertId();
        
        // Add order items
        foreach ($cart_items as $item) {
            $db->query('INSERT INTO order_items (order_id, product_id, quantity, price) 
                       VALUES (:order_id, :product_id, :quantity, :price)');
            $db->bind(':order_id', $order_id);
            $db->bind(':product_id', $item['product_id']);
            $db->bind(':quantity', $item['quantity']);
            $db->bind(':price', $item['price']);
            $db->execute();
            
            // Update product stock
            $db->query('UPDATE products SET stock = stock - :quantity WHERE id = :id');
            $db->bind(':quantity', $item['quantity']);
            $db->bind(':id', $item['product_id']);
            $db->execute();
        }
        
        // Clear cart
        $db->query('DELETE FROM cart WHERE user_id = :user_id');
        $db->bind(':user_id', $user_id);
        $db->execute();
        
        // Create payment record
        $db->query('INSERT INTO payments (order_id, amount, payment_method, status) 
                   VALUES (:order_id, :amount, :payment_method, :status)');
        $db->bind(':order_id', $order_id);
        $db->bind(':amount', $total);
        $db->bind(':payment_method', $payment_method);
        $db->bind(':status', 'pending');
        $db->execute();
        
        $db->commit();
        
        $_SESSION['success'] = 'Order placed successfully! Order Code: ' . $order_code;
        $_SESSION['order_placed'] = true;
        $_SESSION['order_id'] = $order_id;
        
        // Redirect to payment or order confirmation
        if ($payment_method === 'cash_on_delivery') {
            header('Location: order_confirmation.php?id=' . $order_id);
        } else {
            header('Location: payment.php?order_id=' . $order_id);
        }
        exit();
        
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['error'] = 'Checkout failed: ' . $e->getMessage();
    }
}

$cart_count = getCartCount();
$page_title = 'Checkout';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Aurelian Restaurant</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: #0b3b2e;
            --secondary-color: #0f5132;
            --accent-color: #d4af37;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        
        .checkout-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .checkout-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: var(--accent-color);
        }
        
        /* Order Summary */
        .order-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-item-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }
        
        .order-item-info {
            flex: 1;
        }
        
        .order-item-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
            font-size: 1rem;
        }
        
        .order-item-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 8px;
        }
        
        .order-item-quantity {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .order-item-price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        /* Payment Methods */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .payment-method {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            background: white;
        }
        
        .payment-method:hover {
            border-color: var(--accent-color);
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .payment-method.selected {
            border-color: var(--accent-color);
            background: rgba(212, 175, 55, 0.05);
        }
        
        .payment-icon {
            font-size: 32px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .payment-name {
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }
        
        .payment-desc {
            color: #6c757d;
            font-size: 0.85rem;
        }
        
        /* Form Styles */
        .form-label {
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select, .form-textarea {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s;
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus, .form-textarea:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }
        
        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        /* Price Summary */
        .price-summary {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .price-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px dashed #dee2e6;
        }
        
        .price-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .price-row.total {
            border-top: 2px solid var(--primary-color);
            padding-top: 15px;
            margin-top: 15px;
            font-size: 1.2rem;
            font-weight: 700;
        }
        
        .price-label {
            color: #6c757d;
        }
        
        .price-value {
            font-weight: 600;
            color: #333;
        }
        
        .price-value.total {
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        /* Action Buttons */
        .btn-checkout {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 16px 40px;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            transition: all 0.3s;
            font-size: 1.1rem;
            width: 100%;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-checkout:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(11, 59, 46, 0.2);
        }
        
        .btn-checkout:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .btn-back {
            background: #f8f9fa;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-back:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        /* Alert Container */
        .alert-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }
        
        .cart-count {
            background: #dc3545;
            color: white;
            font-size: 0.7rem;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: -10px;
            right: -10px;
            font-weight: bold;
        }
        
        /* Shipping Options */
        .shipping-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .shipping-option {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }
        
        .shipping-option:hover {
            border-color: var(--accent-color);
            transform: translateY(-3px);
        }
        
        .shipping-option.selected {
            border-color: var(--accent-color);
            background: rgba(212, 175, 55, 0.05);
        }
        
        /* Responsive Design */
        @media (max-width: 992px) {
            .checkout-header {
                padding: 50px 0;
            }
            
            .checkout-card {
                padding: 25px;
            }
            
            .payment-methods {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .checkout-header {
                padding: 40px 0;
                margin-bottom: 30px;
            }
            
            .checkout-card {
                padding: 20px;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
            
            .order-item {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .order-item-img {
                margin-right: 0;
                margin-bottom: 10px;
            }
            
            .order-item-details {
                flex-direction: column;
                gap: 5px;
            }
            
            .btn-checkout {
                padding: 14px 30px;
                font-size: 1rem;
            }
        }
        
        @media (max-width: 576px) {
            .checkout-header {
                padding: 30px 0;
            }
            
            .section-title {
                font-size: 1.2rem;
            }
            
            .btn-checkout {
                padding: 12px 25px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-utensils me-2"></i>Aurelian Restaurant
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu.php">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">My Orders</a>
                    </li>
                    <li class="nav-item position-relative">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i> <?php echo $_SESSION['full_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Alert Container -->
    <div class="alert-container">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                <?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </div>
    
    <!-- Checkout Header -->
    <div class="checkout-header">
        <div class="container">
            <h1 class="display-5 fw-bold mb-3">Checkout</h1>
            <p class="lead">Complete your order with secure payment</p>
        </div>
    </div>
    
    <!-- Checkout Content -->
    <div class="container checkout-container py-4">
        <form method="POST" id="checkoutForm">
            <div class="row">
                <!-- Left Column: Order Details & Information -->
                <div class="col-lg-8 mb-4">
                    <!-- Order Summary -->
                    <div class="checkout-card">
                        <h3 class="section-title"><i class="fas fa-shopping-cart"></i> Order Summary</h3>
                        
                        <div class="order-summary">
                            <?php foreach ($cart_items as $item): ?>
                            <div class="order-item">
                                <img src="<?php echo getProductImage($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="order-item-img">
                                <div class="order-item-info">
                                    <div class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="order-item-details">
                                        <span class="order-item-quantity">Quantity: <?php echo $item['quantity']; ?></span>
                                        <span class="order-item-price"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="text-end mt-3">
                            <a href="cart.php" class="btn btn-back">
                                <i class="fas fa-arrow-left me-2"></i> Back to Cart
                            </a>
                        </div>
                    </div>
                    
                    <!-- Delivery Information -->
                    <div class="checkout-card">
                        <h3 class="section-title"><i class="fas fa-truck"></i> Delivery Information</h3>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['full_name']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Delivery Address *</label>
                                <textarea class="form-control form-textarea" name="delivery_address" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                <small class="text-muted">Please provide complete address for delivery</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="checkout-card">
                        <h3 class="section-title"><i class="fas fa-credit-card"></i> Payment Method</h3>
                        
                        <div class="payment-methods">
                            <div class="payment-method" data-method="bank_transfer">
                                <div class="payment-icon"><i class="fas fa-university"></i></div>
                                <div class="payment-name">Bank Transfer</div>
                                <div class="payment-desc">Transfer to our bank account</div>
                                <input type="radio" name="payment_method" value="bank_transfer" required style="display: none;">
                            </div>
                            
                            <div class="payment-method" data-method="credit_card">
                                <div class="payment-icon"><i class="fas fa-credit-card"></i></div>
                                <div class="payment-name">Credit Card</div>
                                <div class="payment-desc">Pay with your credit card</div>
                                <input type="radio" name="payment_method" value="credit_card" style="display: none;">
                            </div>
                            
                            <div class="payment-method" data-method="e_wallet">
                                <div class="payment-icon"><i class="fas fa-mobile-alt"></i></div>
                                <div class="payment-name">E-Wallet</div>
                                <div class="payment-desc">Gopay, OVO, Dana, etc.</div>
                                <input type="radio" name="payment_method" value="e_wallet" style="display: none;">
                            </div>
                            
                            <div class="payment-method" data-method="cash_on_delivery">
                                <div class="payment-icon"><i class="fas fa-money-bill-wave"></i></div>
                                <div class="payment-name">Cash on Delivery</div>
                                <div class="payment-desc">Pay when order arrives</div>
                                <input type="radio" name="payment_method" value="cash_on_delivery" style="display: none;">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional Notes -->
                    <div class="checkout-card">
                        <h3 class="section-title"><i class="fas fa-sticky-note"></i> Additional Notes</h3>
                        
                        <div class="mb-3">
                            <label class="form-label">Special Instructions (Optional)</label>
                            <textarea class="form-control form-textarea" name="notes" placeholder="e.g., Delivery instructions, dietary restrictions, etc."></textarea>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Order Summary & Checkout -->
                <div class="col-lg-4">
                    <div class="checkout-card" style="position: sticky; top: 20px;">
                        <h3 class="section-title"><i class="fas fa-receipt"></i> Price Summary</h3>
                        
                        <div class="price-summary">
                            <div class="price-row">
                                <span class="price-label">Subtotal</span>
                                <span class="price-value"><?php echo formatCurrency($subtotal); ?></span>
                            </div>
                            
                            <div class="price-row">
                                <span class="price-label">Tax (10%)</span>
                                <span class="price-value"><?php echo formatCurrency($tax); ?></span>
                            </div>
                            
                            <div class="price-row">
                                <span class="price-label">Shipping Fee</span>
                                <span class="price-value"><?php echo formatCurrency($shipping); ?></span>
                            </div>
                            
                            <div class="price-row total">
                                <span class="price-label">Total Amount</span>
                                <span class="price-value total"><?php echo formatCurrency($total); ?></span>
                            </div>
                        </div>
                        
                        <!-- Terms & Conditions -->
                        <div class="mt-4">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="termsCheck" required>
                                <label class="form-check-label" for="termsCheck">
                                    I agree to the <a href="#" class="text-primary">Terms & Conditions</a> and <a href="#" class="text-primary">Privacy Policy</a>
                                </label>
                            </div>
                            
                            <div class="form-check mb-4">
                                <input class="form-check-input" type="checkbox" id="newsletterCheck">
                                <label class="form-check-label" for="newsletterCheck">
                                    Subscribe to newsletter for special offers
                                </label>
                            </div>
                        </div>
                        
                        <!-- Security Info -->
                        <div class="alert alert-info border-0 bg-light mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shield-alt me-3 fs-4 text-primary"></i>
                                <div>
                                    <h6 class="mb-1">Secure Payment</h6>
                                    <p class="mb-0 small">Your payment information is protected with SSL encryption</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Checkout Button -->
                        <button type="submit" name="process_checkout" class="btn-checkout">
                            <i class="fas fa-lock me-2"></i> Complete Order
                        </button>
                        
                        <div class="text-center mt-3">
                            <small class="text-muted">
                                <i class="fas fa-undo me-1"></i>
                                You can cancel your order within 1 hour
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Aurelian Restaurant</h5>
                    <p class="mb-0">Fine dining experience with premium cuisine.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0">&copy; 2024 Aurelian Restaurant. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Payment method selection
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                // Remove selected class from all methods
                document.querySelectorAll('.payment-method').forEach(m => {
                    m.classList.remove('selected');
                });
                
                // Add selected class to clicked method
                this.classList.add('selected');
                
                // Update radio button
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                }
            });
        });
        
        // Set default selected payment method
        document.querySelector('.payment-method[data-method="bank_transfer"]').classList.add('selected');
        document.querySelector('.payment-method[data-method="bank_transfer"] input').checked = true;
        
        // Shipping option selection
        document.querySelectorAll('.shipping-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.shipping-option').forEach(o => {
                    o.classList.remove('selected');
                });
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Update radio button
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                }
            });
        });
        
        // Form validation
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const termsCheck = document.getElementById('termsCheck');
            const address = document.querySelector('[name="delivery_address"]');
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!termsCheck.checked) {
                e.preventDefault();
                alert('Please agree to the Terms & Conditions to proceed.');
                termsCheck.focus();
                return false;
            }
            
            if (!address.value.trim()) {
                e.preventDefault();
                alert('Please provide a delivery address.');
                address.focus();
                return false;
            }
            
            if (!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method.');
                return false;
            }
            
            return true;
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Show payment method details
        function showPaymentDetails(method) {
            const details = {
                'bank_transfer': 'Bank: BCA<br>Account: 1234567890<br>Name: Aurelian Restaurant',
                'credit_card': 'We accept Visa, MasterCard, and JCB',
                'e_wallet': 'Available: Gopay, OVO, Dana, LinkAja',
                'cash_on_delivery': 'Pay with cash when your order arrives'
            };
            
            const detailElement = document.getElementById('paymentDetails');
            if (detailElement && details[method]) {
                detailElement.innerHTML = details[method];
                detailElement.style.display = 'block';
            }
        }
        
        // Initialize payment details
        document.querySelectorAll('.payment-method').forEach(method => {
            method.addEventListener('click', function() {
                const methodType = this.dataset.method;
                showPaymentDetails(methodType);
            });
        });
        
        // Show initial payment details
        showPaymentDetails('bank_transfer');
    </script>
</body>
</html>