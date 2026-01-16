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

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_GET['id']);

// Get order details
$db->query('SELECT o.*, u.full_name, u.email, u.phone, u.address 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = :id AND o.user_id = :user_id');
$db->bind(':id', $order_id);
$db->bind(':user_id', $user_id);
$order = $db->single();

if (!$order) {
    header('Location: orders.php');
    exit();
}

// Get order items
$db->query('SELECT oi.*, p.name, p.image, p.description 
            FROM order_items oi 
            JOIN products p ON oi.product_id = p.id 
            WHERE oi.order_id = :order_id');
$db->bind(':order_id', $order_id);
$order_items = $db->resultSet();

// Get payment info
$db->query('SELECT * FROM payments WHERE order_id = :order_id ORDER BY created_at DESC LIMIT 1');
$db->bind(':order_id', $order_id);
$payment = $db->single();

$cart_count = getCartCount();
$page_title = 'Order Details - ' . $order['order_code'];
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
        }
        
        .order-details-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }
        
        .order-status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .info-card h6 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .info-item {
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
        }
        
        .info-item:last-child {
            margin-bottom: 0;
        }
        
        .info-label {
            color: #6c757d;
            font-weight: 500;
        }
        
        .info-value {
            font-weight: 600;
            color: #333;
        }
        
        .item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <!-- Navigation (sama seperti orders.php) -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-utensils me-2"></i>Aurelian Restaurant
            </a>
            
            <div class="collapse navbar-collapse">
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
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Order Details -->
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Order Details</h1>
            <a href="orders.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i> Back to Orders
            </a>
        </div>
        
        <div class="order-details-card">
            <!-- Order Header -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h3 class="mb-2"><?php echo $order['order_code']; ?></h3>
                    <p class="text-muted mb-0">
                        <i class="far fa-calendar me-1"></i>
                        <?php echo date('F d, Y - h:i A', strtotime($order['created_at'])); ?>
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="order-status-badge status-<?php echo $order['status']; ?>">
                        <?php echo strtoupper($order['status']); ?>
                    </span>
                </div>
            </div>
            
            <!-- Customer Info -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="info-card">
                        <h6><i class="fas fa-user me-2"></i>Customer Information</h6>
                        <div class="info-item">
                            <span class="info-label">Name:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['full_name']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['email']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Phone:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['phone']); ?></span>
                        </div>
                        <?php if ($order['address']): ?>
                        <div class="info-item">
                            <span class="info-label">Address:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['address']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="info-card">
                        <h6><i class="fas fa-shopping-cart me-2"></i>Order Information</h6>
                        <div class="info-item">
                            <span class="info-label">Payment Method:</span>
                            <span class="info-value"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Payment Status:</span>
                            <span class="info-value"><?php echo ucfirst($order['payment_status']); ?></span>
                        </div>
                        <?php if ($payment): ?>
                        <div class="info-item">
                            <span class="info-label">Transaction ID:</span>
                            <span class="info-value"><?php echo $payment['transaction_id'] ?? 'N/A'; ?></span>
                        </div>
                        <?php endif; ?>
                        <?php if ($order['notes']): ?>
                        <div class="info-item">
                            <span class="info-label">Notes:</span>
                            <span class="info-value"><?php echo htmlspecialchars($order['notes']); ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Order Items -->
            <div class="mb-4">
                <h5 class="mb-3">Order Items</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="100">Image</th>
                                <th>Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo getProductImage($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="item-img">
                                </td>
                                <td>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p class="small text-muted mb-0"><?php echo substr($item['description'], 0, 100); ?>...</p>
                                </td>
                                <td class="text-center"><?php echo $item['quantity']; ?></td>
                                <td class="text-center"><?php echo formatCurrency($item['price']); ?></td>
                                <td class="text-end"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="row justify-content-end">
                <div class="col-md-6">
                    <div class="info-card">
                        <h6><i class="fas fa-receipt me-2"></i>Order Summary</h6>
                        <div class="info-item">
                            <span class="info-label">Subtotal:</span>
                            <span class="info-value"><?php echo formatCurrency($order['total_amount']); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Tax (10%):</span>
                            <span class="info-value"><?php echo formatCurrency($order['total_amount'] * 0.10); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Shipping:</span>
                            <span class="info-value"><?php echo formatCurrency(15000); ?></span>
                        </div>
                        <div class="info-item" style="border-top: 2px solid #dee2e6; padding-top: 10px;">
                            <span class="info-label fw-bold">Total Amount:</span>
                            <span class="info-value fw-bold fs-5"><?php echo formatCurrency($order['total_amount'] + ($order['total_amount'] * 0.10) + 15000); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <?php if ($order['status'] === 'pending'): ?>
            <div class="text-end mt-4">
                <form method="POST" action="orders.php" class="d-inline">
                    <input type="hidden" name="action" value="cancel_order">
                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                    <button type="submit" class="btn btn-danger" 
                            onclick="return confirm('Are you sure you want to cancel this order?')">
                        <i class="fas fa-times me-2"></i> Cancel Order
                    </button>
                </form>
                
                <?php if ($order['payment_status'] === 'pending'): ?>
                <a href="payment.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary ms-2">
                    <i class="fas fa-credit-card me-2"></i> Make Payment
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>