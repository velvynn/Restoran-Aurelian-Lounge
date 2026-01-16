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

// Handle cart actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                if (isset($_POST['item_id']) && isset($_POST['quantity'])) {
                    $item_id = intval($_POST['item_id']);
                    $quantity = intval($_POST['quantity']);
                    
                    if ($quantity > 0) {
                        $db->query('UPDATE cart SET quantity = :quantity WHERE id = :id AND user_id = :user_id');
                        $db->bind(':quantity', $quantity);
                        $db->bind(':id', $item_id);
                        $db->bind(':user_id', $user_id);
                        $db->execute();
                        $_SESSION['success'] = 'Cart updated successfully!';
                    } else {
                        $db->query('DELETE FROM cart WHERE id = :id AND user_id = :user_id');
                        $db->bind(':id', $item_id);
                        $db->bind(':user_id', $user_id);
                        $db->execute();
                        $_SESSION['success'] = 'Item removed from cart!';
                    }
                }
                break;
                
            case 'remove':
                if (isset($_POST['item_id'])) {
                    $item_id = intval($_POST['item_id']);
                    $db->query('DELETE FROM cart WHERE id = :id AND user_id = :user_id');
                    $db->bind(':id', $item_id);
                    $db->bind(':user_id', $user_id);
                    $db->execute();
                    $_SESSION['success'] = 'Item removed from cart!';
                }
                break;
                
            case 'clear':
                $db->query('DELETE FROM cart WHERE user_id = :user_id');
                $db->bind(':user_id', $user_id);
                $db->execute();
                $_SESSION['success'] = 'Cart cleared successfully!';
                break;
        }
        
        header('Location: cart.php');
        exit();
    }
}

// Get cart items from database
$db->query('SELECT c.*, p.name, p.price, p.image, p.stock, p.description 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = :user_id 
            ORDER BY c.created_at DESC');
$db->bind(':user_id', $user_id);
$cart_items = $db->resultSet();

// Calculate totals
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$tax = $subtotal * 0.10; // 10% tax
$shipping = $subtotal > 0 ? 15000 : 0; // Shipping fee
$total = $subtotal + $tax + $shipping;

$cart_count = getCartCount();
$page_title = 'Shopping Cart';
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
        
        .cart-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .cart-item {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            transition: all 0.3s;
        }
        
        .cart-item:hover {
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }
        
        .product-img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #f0f0f0;
        }
        
        .product-info h5 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .product-info p {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 10px;
        }
        
        .stock-info {
            font-size: 0.85rem;
            padding: 3px 10px;
            border-radius: 20px;
        }
        
        .stock-low {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-ok {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .quantity-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .quantity-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: scale(1.1);
        }
        
        .quantity-input {
            width: 70px;
            text-align: center;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 8px;
            font-weight: 600;
            font-size: 1rem;
        }
        
        .quantity-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(11, 59, 46, 0.25);
            outline: none;
        }
        
        .item-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 2px solid #f0f0f0;
            position: sticky;
            top: 30px;
        }
        
        .summary-card h4 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #e9ecef;
        }
        
        .summary-item.total {
            border-bottom: 2px solid var(--primary-color);
            padding-top: 15px;
            margin-top: 15px;
        }
        
        .summary-item.total .amount {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .btn-checkout {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 16px;
            font-weight: 700;
            width: 100%;
            border: none;
            border-radius: 10px;
            transition: all 0.3s;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            margin-top: 20px;
        }
        
        .btn-checkout:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(11, 59, 46, 0.2);
        }
        
        .btn-checkout:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }
        
        .empty-cart {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .empty-cart i {
            font-size: 100px;
            color: #dee2e6;
            margin-bottom: 30px;
            opacity: 0.7;
        }
        
        .empty-cart h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .empty-cart p {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        
        .btn-shopping {
            background: var(--primary-color);
            color: white;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-shopping:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
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
        
        .alert-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }
        
        .btn-remove {
            background: #dc3545;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .btn-remove:hover {
            background: #c82333;
            transform: scale(1.1);
        }
        
        .mobile-actions {
            display: none;
        }
        
        @media (max-width: 768px) {
            .cart-item {
                padding: 15px;
            }
            
            .product-img {
                width: 80px;
                height: 80px;
            }
            
            .desktop-actions {
                display: none;
            }
            
            .mobile-actions {
                display: block;
                margin-top: 15px;
                padding-top: 15px;
                border-top: 1px solid #e9ecef;
            }
            
            .quantity-controls {
                justify-content: center;
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
    
    <!-- Cart Header -->
    <div class="cart-header">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">Shopping Cart</h1>
            <p class="lead">Review your items and proceed to checkout</p>
        </div>
    </div>
    
    <!-- Cart Content -->
    <div class="container py-4">
        <?php if (!empty($cart_items)): ?>
            <div class="row">
                <!-- Cart Items -->
                <div class="col-lg-8 mb-4">
                    <?php foreach ($cart_items as $item): ?>
                        <div class="cart-item">
                            <div class="row align-items-center">
                                <div class="col-md-2 mb-3 mb-md-0">
                                    <img src="<?php echo getProductImage($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="product-img">
                                </div>
                                <div class="col-md-4 mb-3 mb-md-0">
                                    <div class="product-info">
                                        <h5><?php echo htmlspecialchars($item['name']); ?></h5>
                                        <p class="mb-1 text-primary fw-bold"><?php echo formatCurrency($item['price']); ?></p>
                                        <p class="small text-muted mb-2">
                                            <?php echo substr($item['description'], 0, 80) . '...'; ?>
                                        </p>
                                        <span class="stock-info <?php echo $item['stock'] < 10 ? 'stock-low' : 'stock-ok'; ?>">
                                            <i class="fas fa-box me-1"></i>
                                            <?php echo $item['stock']; ?> in stock
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Desktop Actions -->
                                <div class="col-md-4 desktop-actions">
                                    <div class="quantity-controls">
                                        <form method="POST" class="d-flex align-items-center justify-content-center">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                            
                                            <button type="button" class="quantity-btn minus" onclick="updateQuantity(this, -1)">-</button>
                                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                   min="1" max="<?php echo min(10, $item['stock']); ?>" 
                                                   class="form-control quantity-input" 
                                                   onchange="this.form.submit()">
                                            <button type="button" class="quantity-btn plus" onclick="updateQuantity(this, 1)">+</button>
                                            
                                            <button type="submit" style="display: none;">Update</button>
                                        </form>
                                    </div>
                                </div>
                                
                                <div class="col-md-2 desktop-actions text-end">
                                    <div class="item-total">
                                        <?php echo formatCurrency($item['price'] * $item['quantity']); ?>
                                    </div>
                                    <form method="POST" class="mt-2">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <button type="submit" class="btn btn-remove" 
                                                onclick="return confirm('Remove this item from cart?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Mobile Actions -->
                                <div class="col-12 mobile-actions">
                                    <div class="row align-items-center">
                                        <div class="col-6">
                                            <div class="quantity-controls">
                                                <form method="POST">
                                                    <input type="hidden" name="action" value="update">
                                                    <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                    <div class="d-flex align-items-center">
                                                        <button type="button" class="quantity-btn minus" onclick="updateQuantity(this, -1)">-</button>
                                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                                               min="1" max="<?php echo min(10, $item['stock']); ?>" 
                                                               class="form-control quantity-input" 
                                                               onchange="this.form.submit()">
                                                        <button type="button" class="quantity-btn plus" onclick="updateQuantity(this, 1)">+</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                        <div class="col-6 text-end">
                                            <div class="item-total mb-2">
                                                <?php echo formatCurrency($item['price'] * $item['quantity']); ?>
                                            </div>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                        onclick="return confirm('Remove this item from cart?')">
                                                    <i class="fas fa-trash me-1"></i> Remove
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Cart Actions -->
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <a href="menu.php" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left me-2"></i> Continue Shopping
                        </a>
                        
                        <div>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="btn btn-outline-danger" 
                                        onclick="return confirm('Clear all items from cart?')">
                                    <i class="fas fa-trash-alt me-2"></i> Clear Cart
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="summary-card">
                        <h4>Order Summary</h4>
                        
                        <div class="summary-item">
                            <span>Subtotal</span>
                            <span class="fw-bold"><?php echo formatCurrency($subtotal); ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span>Tax (10%)</span>
                            <span class="fw-bold"><?php echo formatCurrency($tax); ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span>Shipping Fee</span>
                            <span class="fw-bold"><?php echo formatCurrency($shipping); ?></span>
                        </div>
                        
                        <div class="summary-item total">
                            <span class="fw-bold">Total Amount</span>
                            <span class="amount"><?php echo formatCurrency($total); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="btn btn-checkout">
                            <i class="fas fa-lock me-2"></i> Proceed to Checkout
                        </a>
                        
                        <div class="mt-4 text-center">
                            <small class="text-muted">
                                <i class="fas fa-shield-alt me-1"></i> Secure SSL Encryption
                            </small>
                            <p class="small mt-2">
                                <i class="fas fa-info-circle me-1"></i>
                                Free shipping on orders over Rp 200.000
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Your cart is empty</h3>
                <p class="text-muted mb-4">Add some delicious items from our menu to get started</p>
                <a href="menu.php" class="btn-shopping">
                    <i class="fas fa-utensils me-2"></i> Browse Our Menu
                </a>
            </div>
        <?php endif; ?>
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
        // Function to update quantity
        function updateQuantity(button, change) {
            const form = button.closest('form');
            const input = form.querySelector('input[name="quantity"]');
            let quantity = parseInt(input.value);
            const max = parseInt(input.max);
            const min = parseInt(input.min);
            
            quantity += change;
            quantity = Math.max(min, Math.min(max, quantity));
            
            input.value = quantity;
            
            // Auto-submit form if quantity changed
            if (input.value != quantity) {
                form.submit();
            }
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Prevent form submission on enter in quantity inputs
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>