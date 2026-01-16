<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Cek apakah user adalah admin
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'manager'])) {
    header('Location: ../login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = intval($_GET['id']);

// Get order details
$db->query('SELECT o.*, u.full_name, u.email, u.phone, u.address 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            WHERE o.id = :id');
$db->bind(':id', $order_id);
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

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $status = sanitize($_POST['status']);
        
        $db->query('UPDATE orders SET status = :status WHERE id = :id');
        $db->bind(':status', $status);
        $db->bind(':id', $order_id);
        
        if ($db->execute()) {
            $_SESSION['success'] = 'Order status updated!';
            header('Location: order_details.php?id=' . $order_id);
            exit();
        }
    }
    
    if (isset($_POST['update_payment'])) {
        $payment_status = sanitize($_POST['payment_status']);
        
        $db->query('UPDATE orders SET payment_status = :payment_status WHERE id = :id');
        $db->bind(':payment_status', $payment_status);
        $db->bind(':id', $order_id);
        
        if ($db->execute()) {
            $_SESSION['success'] = 'Payment status updated!';
            header('Location: order_details.php?id=' . $order_id);
            exit();
        }
    }
}

$page_title = 'Order Details - ' . $order['order_code'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - <?php echo SITE_NAME; ?></title>
    
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
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .content-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .order-details-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-pending { background: #FFF3CD; color: #856404; }
        .status-processing { background: #D1ECF1; color: #0C5460; }
        .status-completed { background: #D4EDDA; color: #155724; }
        .status-cancelled { background: #F8D7DA; color: #721C24; }
        
        .payment-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .payment-pending { background: #FFF3CD; color: #856404; }
        .payment-paid { background: #D4EDDA; color: #155724; }
        .payment-failed { background: #F8D7DA; color: #721C24; }
        
        .info-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .item-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -35px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: var(--primary-color);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="main-content">
                <div class="content-header">
                    <h1>Order Details</h1>
                    <a href="orders.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Orders
                    </a>
                </div>
                
                <?php displayMessage(); ?>
                
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
                            <div class="d-flex flex-column align-items-md-end gap-2">
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo strtoupper($order['status']); ?>
                                </span>
                                <span class="payment-badge payment-<?php echo $order['payment_status']; ?>">
                                    Payment: <?php echo strtoupper($order['payment_status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Customer & Order Info -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6><i class="fas fa-user me-2"></i>Customer Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td width="120"><strong>Name:</strong></td>
                                        <td><?php echo htmlspecialchars($order['full_name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email:</strong></td>
                                        <td><?php echo htmlspecialchars($order['email']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Phone:</strong></td>
                                        <td><?php echo htmlspecialchars($order['phone']); ?></td>
                                    </tr>
                                    <?php if ($order['address']): ?>
                                    <tr>
                                        <td><strong>Address:</strong></td>
                                        <td><?php echo htmlspecialchars($order['address']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6><i class="fas fa-shopping-cart me-2"></i>Order Information</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td width="120"><strong>Payment Method:</strong></td>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Payment Status:</strong></td>
                                        <td><?php echo ucfirst($order['payment_status']); ?></td>
                                    </tr>
                                    <?php if ($order['notes']): ?>
                                    <tr>
                                        <td><strong>Notes:</strong></td>
                                        <td><?php echo htmlspecialchars($order['notes']); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </table>
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
                                <table class="table table-sm">
                                    <tr>
                                        <td><strong>Subtotal:</strong></td>
                                        <td class="text-end"><?php echo formatCurrency($order['total_amount']); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Tax (10%):</strong></td>
                                        <td class="text-end"><?php echo formatCurrency($order['total_amount'] * 0.10); ?></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Shipping:</strong></td>
                                        <td class="text-end"><?php echo formatCurrency(15000); ?></td>
                                    </tr>
                                    <tr class="table-active">
                                        <td><strong>Total Amount:</strong></td>
                                        <td class="text-end fw-bold fs-5"><?php echo formatCurrency($order['total_amount'] + ($order['total_amount'] * 0.10) + 15000); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Order Timeline -->
                    <div class="mb-4">
                        <h5 class="mb-3">Order Timeline</h5>
                        <div class="timeline">
                            <div class="timeline-item">
                                <h6 class="mb-1">Order Placed</h6>
                                <p class="text-muted mb-0"><?php echo date('F d, Y - h:i A', strtotime($order['created_at'])); ?></p>
                            </div>
                            
                            <?php if ($order['status'] !== 'pending'): ?>
                            <div class="timeline-item">
                                <h6 class="mb-1">Order Processed</h6>
                                <p class="text-muted mb-0"><?php echo date('F d, Y - h:i A', strtotime($order['updated_at'])); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ($order['status'] === 'completed'): ?>
                            <div class="timeline-item">
                                <h6 class="mb-1">Order Completed</h6>
                                <p class="text-muted mb-0">Order delivered to customer</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6><i class="fas fa-cog me-2"></i>Update Order Status</h6>
                                <form method="POST" class="row g-3">
                                    <div class="col-md-8">
                                        <select name="status" class="form-select" required>
                                            <option value="pending" <?php echo $order['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="processing" <?php echo $order['status'] === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="completed" <?php echo $order['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $order['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" name="update_status" class="btn btn-primary w-100">
                                            Update
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="info-card">
                                <h6><i class="fas fa-credit-card me-2"></i>Update Payment Status</h6>
                                <form method="POST" class="row g-3">
                                    <div class="col-md-8">
                                        <select name="payment_status" class="form-select" required>
                                            <option value="pending" <?php echo $order['payment_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="paid" <?php echo $order['payment_status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                            <option value="failed" <?php echo $order['payment_status'] === 'failed' ? 'selected' : ''; ?>>Failed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" name="update_payment" class="btn btn-primary w-100">
                                            Update
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>