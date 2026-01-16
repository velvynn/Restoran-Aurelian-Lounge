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

// Handle order actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $order_id = intval($_POST['order_id']);
        $status = sanitize($_POST['status']);
        
        $db->query('UPDATE orders SET status = :status WHERE id = :id');
        $db->bind(':status', $status);
        $db->bind(':id', $order_id);
        
        if ($db->execute()) {
            $_SESSION['success'] = 'Order status updated!';
        }
    }
    
    if (isset($_POST['update_payment_status'])) {
        $order_id = intval($_POST['order_id']);
        $payment_status = sanitize($_POST['payment_status']);
        
        $db->query('UPDATE orders SET payment_status = :payment_status WHERE id = :id');
        $db->bind(':payment_status', $payment_status);
        $db->bind(':id', $order_id);
        
        if ($db->execute()) {
            $_SESSION['success'] = 'Payment status updated!';
        }
    }
    
    header('Location: orders.php');
    exit();
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? '';

// Build query
$query = 'SELECT o.*, u.full_name, u.email, u.phone FROM orders o 
          LEFT JOIN users u ON o.user_id = u.id';
          
$where_clauses = [];
$params = [];

if ($status_filter !== 'all') {
    $where_clauses[] = 'o.status = :status';
    $params[':status'] = $status_filter;
}

if ($date_filter) {
    $where_clauses[] = 'DATE(o.created_at) = :date';
    $params[':date'] = $date_filter;
}

if (!empty($where_clauses)) {
    $query .= ' WHERE ' . implode(' AND ', $where_clauses);
}

$query .= ' ORDER BY o.created_at DESC';

$db->query($query);
foreach ($params as $key => $value) {
    $db->bind($key, $value);
}
$orders = $db->resultSet();

// Get statistics
$db->query('SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending_orders,
    SUM(CASE WHEN status = "processing" THEN 1 ELSE 0 END) as processing_orders,
    SUM(CASE WHEN status = "completed" THEN 1 ELSE 0 END) as completed_orders,
    SUM(CASE WHEN status = "cancelled" THEN 1 ELSE 0 END) as cancelled_orders,
    SUM(total_amount) as total_revenue
    FROM orders');
$stats = $db->single();

$page_title = 'Manage Orders';
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
            overflow-x: hidden;
        }
        
        .main-content {
            margin-left: 280px;
            padding: 20px;
            min-height: 100vh;
            width: max-content;
            min-width: calc(100vw - 280px);
        }
        
        .content-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            min-width: 800px;
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            min-width: 800px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card h3 {
            font-size: 28px;
            margin: 0;
            color: var(--primary-color);
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-pending { background: #FFF3CD; color: #856404; }
        .status-processing { background: #D1ECF1; color: #0C5460; }
        .status-completed { background: #D4EDDA; color: #155724; }
        .status-cancelled { background: #F8D7DA; color: #721C24; }
        
        .payment-status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .payment-pending { background: #FFF3CD; color: #856404; }
        .payment-paid { background: #D4EDDA; color: #155724; }
        .payment-failed { background: #F8D7DA; color: #721C24; }
        
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            min-width: 800px;
        }
        
        .orders-table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            min-width: 800px;
        }
        
        .table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .dropdown-menu {
            border-radius: 10px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .dropdown-item {
            padding: 8px 15px;
            font-size: 0.9rem;
        }
        
        .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                min-width: 100%;
            }
            
            .content-header,
            .stats-cards,
            .filter-card,
            .orders-table-container {
                min-width: 100%;
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
                    <h1>Manage Orders</h1>
                    <p class="text-muted mb-0">View and manage customer orders</p>
                </div>
                
                <?php displayMessage(); ?>
                
                <!-- Statistics -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3><?php echo $stats['total_orders']; ?></h3>
                        <p>Total Orders</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['pending_orders']; ?></h3>
                        <p>Pending</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['processing_orders']; ?></h3>
                        <p>Processing</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $stats['completed_orders']; ?></h3>
                        <p>Completed</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo formatCurrency($stats['total_revenue']); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="filter-card">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label>Order Status</label>
                            <select name="status" class="form-select">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>All Status</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $status_filter === 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label>Date</label>
                            <input type="date" name="date" class="form-control" value="<?php echo $date_filter; ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Filter</button>
                            <a href="orders.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
                
                <!-- Orders Table -->
                <div class="orders-table-container">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Order Code</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Payment</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="order_details.php?id=<?php echo $order['id']; ?>" class="text-decoration-none">
                                            <?php echo $order['order_code']; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div><?php echo htmlspecialchars($order['full_name']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($order['email']); ?></small>
                                    </td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td>
                                        <span class="status-badge status-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="payment-status-badge payment-<?php echo $order['payment_status']; ?>">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <a class="dropdown-item" href="order_details.php?id=<?php echo $order['id']; ?>">
                                                        <i class="fas fa-eye me-2"></i> View Details
                                                    </a>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <button type="submit" name="update_status" value="processing" class="dropdown-item">
                                                            <i class="fas fa-cog me-2"></i> Mark as Processing
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <button type="submit" name="update_status" value="completed" class="dropdown-item">
                                                            <i class="fas fa-check me-2"></i> Mark as Completed
                                                        </button>
                                                    </form>
                                                </li>
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <button type="submit" name="update_payment_status" value="paid" class="dropdown-item">
                                                            <i class="fas fa-money-bill me-2"></i> Mark as Paid
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>