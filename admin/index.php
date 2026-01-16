<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Cek apakah user adalah admin/manager
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    redirect('../login.php');
}

// Cek role
if (!in_array($_SESSION['role'], ['admin', 'manager'])) {
    $_SESSION['error'] = 'Akses ditolak! Anda tidak memiliki izin untuk mengakses halaman ini.';
    redirect('../dashboard.php');
}

// Ambil data statistik dari database
$db->query('SELECT COUNT(*) as total FROM users WHERE role = "user"');
$total_customers = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM products WHERE is_active = 1');
$total_products = $db->single()['total'];

$db->query('SELECT COUNT(*) as total FROM orders');
$total_orders = $db->single()['total'];

$db->query('SELECT SUM(total_amount) as total FROM orders WHERE status = "completed" AND DATE(created_at) = CURDATE()');
$result = $db->single();
$today_revenue = $result['total'] ? $result['total'] : 0;

// Ambil 5 pesanan terbaru
$db->query('SELECT o.*, u.full_name FROM orders o 
           LEFT JOIN users u ON o.user_id = u.id 
           ORDER BY o.created_at DESC LIMIT 5');
$recent_orders = $db->resultSet();

// Ambil produk terlaris
$db->query('SELECT p.name, p.image, SUM(oi.quantity) as total_sold 
           FROM order_items oi 
           JOIN products p ON oi.product_id = p.id 
           GROUP BY p.id 
           ORDER BY total_sold DESC LIMIT 5');
$best_sellers = $db->resultSet();

$page_title = 'Dashboard Admin';
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
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            min-width: 800px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
            color: white;
        }
        
        .stat-icon.customers { background: linear-gradient(135deg, #4CAF50, #2E7D32); }
        .stat-icon.products { background: linear-gradient(135deg, #2196F3, #0D47A1); }
        .stat-icon.orders { background: linear-gradient(135deg, #FF9800, #EF6C00); }
        .stat-icon.revenue { background: linear-gradient(135deg, #9C27B0, #6A1B9A); }
        
        .stat-info h3 {
            font-size: 28px;
            margin: 0;
            color: #333;
        }
        
        .stat-info p {
            margin: 5px 0 0;
            color: #666;
            font-size: 0.9rem;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .status-pending { background: #FFF3CD; color: #856404; }
        .status-processing { background: #D1ECF1; color: #0C5460; }
        .status-completed { background: #D4EDDA; color: #155724; }
        .status-cancelled { background: #F8D7DA; color: #721C24; }
        
        .dashboard-row {
            display: flex;
            gap: 20px;
            min-width: 800px;
            margin-bottom: 20px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            flex: 1;
        }
        
        .dashboard-card-wide {
            flex: 2;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        .best-sellers-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .best-sellers-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .best-sellers-list li:last-child {
            border-bottom: none;
        }
        
        .rank-badge {
            background: var(--accent-color);
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                min-width: 100%;
            }
            
            .content-header,
            .stats-row,
            .dashboard-row {
                min-width: 100%;
            }
            
            .dashboard-row {
                flex-direction: column;
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
                    <h1>Dashboard</h1>
                    <div class="date"><?php echo date('l, d F Y'); ?></div>
                </div>
                
                <?php displayMessage(); ?>
                
                <!-- Stats Cards -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-icon customers">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_customers; ?></h3>
                            <p>Total Customers</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon products">
                            <i class="fas fa-utensils"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_products; ?></h3>
                            <p>Total Products</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon orders">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo $total_orders; ?></h3>
                            <p>Total Orders</p>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon revenue">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-info">
                            <h3><?php echo formatCurrency($today_revenue); ?></h3>
                            <p>Today's Revenue</p>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders & Best Sellers -->
                <div class="dashboard-row">
                    <div class="dashboard-card dashboard-card-wide">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Recent Orders</h5>
                            <a href="orders.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Order Code</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_orders)): ?>
                                        <?php foreach ($recent_orders as $order): ?>
                                        <tr>
                                            <td><a href="order_details.php?id=<?php echo $order['id']; ?>" class="text-decoration-none"><?php echo $order['order_code']; ?></a></td>
                                            <td><?php echo $order['full_name'] ?? 'Guest'; ?></td>
                                            <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No orders found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="dashboard-card">
                        <h5 class="mb-3">Best Selling Products</h5>
                        <ul class="best-sellers-list">
                            <?php if (!empty($best_sellers)): ?>
                                <?php foreach ($best_sellers as $index => $product): ?>
                                <li>
                                    <div class="product-info">
                                        <h6 class="mb-1"><?php echo $product['name']; ?></h6>
                                        <p class="mb-0 text-muted"><?php echo $product['total_sold']; ?> sold</p>
                                    </div>
                                    <div class="product-rank">
                                        <span class="rank-badge">#<?php echo $index + 1; ?></span>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="text-center">No sales data</li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>