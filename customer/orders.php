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
$cart_count = getCartCount();

// Handle order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'cancel_order':
                if (isset($_POST['order_id'])) {
                    $order_id = intval($_POST['order_id']);
                    $db->query('UPDATE orders SET status = "cancelled" WHERE id = :id AND user_id = :user_id AND status = "pending"');
                    $db->bind(':id', $order_id);
                    $db->bind(':user_id', $user_id);
                    $db->execute();
                    $_SESSION['success'] = 'Order cancelled successfully!';
                }
                break;
        }
        header('Location: orders.php');
        exit();
    }
}

// Get user orders
$db->query('SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC');
$db->bind(':user_id', $user_id);
$orders = $db->resultSet();

// Get order items for each order
foreach ($orders as &$order) {
    $db->query('SELECT oi.*, p.name, p.image FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = :order_id');
    $db->bind(':order_id', $order['id']);
    $order['items'] = $db->resultSet();
}

$page_title = 'My Orders';
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
        
        .orders-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .order-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            border: 1px solid #e9ecef;
            transition: all 0.3s;
        }
        
        .order-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .order-code {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.2rem;
        }
        
        .order-date {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .order-status {
            padding: 6px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            display: inline-block;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-delivered { background: #d4edda; color: #155724; }
        
        .order-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-img {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-info h6 {
            margin-bottom: 5px;
            color: #333;
        }
        
        .item-info p {
            margin-bottom: 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .item-quantity {
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .item-price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        
        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f0f0;
        }
        
        .order-total {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .order-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-order-action {
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        
        .btn-view {
            background: var(--primary-color);
            color: white;
            border: none;
        }
        
        .btn-view:hover {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-cancel {
            background: #f8f9fa;
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        
        .btn-cancel:hover {
            background: #dc3545;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-reorder {
            background: var(--accent-color);
            color: #333;
            border: none;
        }
        
        .btn-reorder:hover {
            background: #e6b800;
            color: #333;
            transform: translateY(-2px);
        }
        
        .empty-orders {
            text-align: center;
            padding: 80px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .empty-orders i {
            font-size: 100px;
            color: #dee2e6;
            margin-bottom: 30px;
            opacity: 0.7;
        }
        
        .empty-orders h3 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .empty-orders p {
            color: #6c757d;
            font-size: 1.1rem;
            margin-bottom: 30px;
        }
        
        .btn-menu {
            background: var(--primary-color);
            color: white;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-menu:hover {
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
        
        .order-tracking {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid var(--primary-color);
        }
        
        .tracking-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin-top: 20px;
        }
        
        .tracking-steps::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }
        
        .tracking-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .step-icon {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.8rem;
            margin-bottom: 8px;
        }
        
        .step-active {
            background: var(--primary-color);
        }
        
        .step-completed {
            background: var(--secondary-color);
        }
        
        .step-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-align: center;
        }
        
        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .order-footer {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .order-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .btn-order-action {
                flex: 1;
                text-align: center;
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
                        <a class="nav-link active" href="orders.php">My Orders</a>
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
    
    <!-- Orders Header -->
    <div class="orders-header">
        <div class="container">
            <h1 class="display-4 fw-bold mb-3">My Orders</h1>
            <p class="lead">Track your orders and view order history</p>
        </div>
    </div>
    
    <!-- Orders Content -->
    <div class="container py-4">
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-card">
                    <!-- Order Header -->
                    <div class="order-header">
                        <div>
                            <div class="order-code"><?php echo $order['order_code']; ?></div>
                            <div class="order-date">
                                <i class="far fa-calendar me-1"></i>
                                <?php echo date('F d, Y - h:i A', strtotime($order['created_at'])); ?>
                            </div>
                        </div>
                        <div class="order-status status-<?php echo $order['status']; ?>">
                            <?php echo strtoupper($order['status']); ?>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="order-items">
                        <?php foreach ($order['items'] as $item): ?>
                            <div class="order-item">
                                <img src="<?php echo getProductImage($item['image']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                     class="item-img">
                                <div class="item-info">
                                    <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p>Quantity: <span class="item-quantity"><?php echo $item['quantity']; ?></span></p>
                                </div>
                                <div class="item-price">
                                    <?php echo formatCurrency($item['price'] * $item['quantity']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Order Tracking (for processing orders) -->
                    <?php if ($order['status'] === 'processing' || $order['status'] === 'completed'): ?>
                        <div class="order-tracking">
                            <h6><i class="fas fa-truck me-2"></i>Order Tracking</h6>
                            <div class="tracking-steps">
                                <?php 
                                $steps = ['Order Placed', 'Processing', 'Preparing', 'On Delivery', 'Delivered'];
                                $current_step = 0;
                                
                                switch($order['status']) {
                                    case 'pending': $current_step = 0; break;
                                    case 'processing': $current_step = 1; break;
                                    case 'completed': $current_step = 4; break;
                                    default: $current_step = 0;
                                }
                                ?>
                                
                                <?php foreach ($steps as $index => $step): ?>
                                    <div class="tracking-step">
                                        <div class="step-icon 
                                            <?php echo $index < $current_step ? 'step-completed' : ''; ?>
                                            <?php echo $index == $current_step ? 'step-active' : ''; ?>">
                                            <?php if ($index < $current_step): ?>
                                                <i class="fas fa-check"></i>
                                            <?php else: ?>
                                                <?php echo $index + 1; ?>
                                            <?php endif; ?>
                                        </div>
                                        <div class="step-label"><?php echo $step; ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Order Footer -->
                    <div class="order-footer">
                        <div class="order-total">
                            Total: <?php echo formatCurrency($order['total_amount']); ?>
                        </div>
                        <div class="order-actions">
                            <a href="order_details.php?id=<?php echo $order['id']; ?>" class="btn btn-order-action btn-view">
                                <i class="fas fa-eye me-1"></i> View Details
                            </a>
                            
                            <?php if ($order['status'] === 'pending'): ?>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="cancel_order">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <button type="submit" class="btn btn-order-action btn-cancel" 
                                            onclick="return confirm('Are you sure you want to cancel this order?')">
                                        <i class="fas fa-times me-1"></i> Cancel Order
                                    </button>
                                </form>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] === 'completed' || $order['status'] === 'cancelled'): ?>
                                <button class="btn btn-order-action btn-reorder" 
                                        onclick="reorder(<?php echo $order['id']; ?>)">
                                    <i class="fas fa-redo me-1"></i> Reorder
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-orders">
                <i class="fas fa-shopping-bag"></i>
                <h3>No orders yet</h3>
                <p class="text-muted mb-4">You haven't placed any orders yet. Start by exploring our menu!</p>
                <a href="menu.php" class="btn-menu">
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
        // Function to reorder
        function reorder(orderId) {
            if (confirm('Add all items from this order to cart?')) {
                fetch('reorder.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'order_id=' + orderId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Items added to cart!');
                        window.location.href = 'cart.php';
                    } else {
                        alert('Error: ' + (data.error || 'Unable to reorder'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while reordering');
                });
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
    </script>
</body>
</html>