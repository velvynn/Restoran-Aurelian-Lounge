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

// Get date range
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date = $_GET['end_date'] ?? date('Y-m-d');

// Get sales statistics
$db->query('SELECT 
    COUNT(*) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order_value,
    COUNT(DISTINCT user_id) as unique_customers
    FROM orders 
    WHERE DATE(created_at) BETWEEN :start_date AND :end_date');
$db->bind(':start_date', $start_date);
$db->bind(':end_date', $end_date);
$sales_stats = $db->single();

// Get daily sales
$db->query('SELECT 
    DATE(created_at) as date,
    COUNT(*) as order_count,
    SUM(total_amount) as daily_revenue
    FROM orders 
    WHERE DATE(created_at) BETWEEN :start_date AND :end_date
    GROUP BY DATE(created_at)
    ORDER BY date');
$db->bind(':start_date', $start_date);
$db->bind(':end_date', $end_date);
$daily_sales = $db->resultSet();

// Get top products
$db->query('SELECT 
    p.name,
    p.category_id,
    c.name as category_name,
    SUM(oi.quantity) as total_sold,
    SUM(oi.quantity * oi.price) as total_revenue
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN categories c ON p.category_id = c.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN :start_date AND :end_date
    GROUP BY p.id
    ORDER BY total_sold DESC
    LIMIT 10');
$db->bind(':start_date', $start_date);
$db->bind(':end_date', $end_date);
$top_products = $db->resultSet();

// Get customer statistics
$db->query('SELECT 
    u.full_name,
    u.email,
    COUNT(o.id) as order_count,
    SUM(o.total_amount) as total_spent,
    MAX(o.created_at) as last_order
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    WHERE u.role = "user"
    GROUP BY u.id
    HAVING order_count > 0
    ORDER BY total_spent DESC
    LIMIT 10');
$top_customers = $db->resultSet();

$page_title = 'Sales Reports';
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
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        
        .filter-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
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
        
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            min-width: 800px;
        }
        
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            min-width: 800px;
        }
        
        .export-buttons {
            margin-bottom: 20px;
            min-width: 800px;
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
            .filter-card,
            .stats-cards,
            .chart-container,
            .table-container,
            .export-buttons {
                min-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .export-buttons {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
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
                    <h1>Sales Reports</h1>
                    <p class="text-muted mb-0">View sales analytics and reports</p>
                </div>
                
                <!-- Date Filter -->
                <div class="filter-card">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label>Start Date</label>
                            <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label>End Date</label>
                            <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>" max="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">Generate Report</button>
                            <a href="reports.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </form>
                </div>
                
                <!-- Export Buttons -->
                <div class="export-buttons">
                    <button class="btn btn-success" onclick="exportToPDF()">
                        <i class="fas fa-file-pdf me-2"></i>Export as PDF
                    </button>
                    <button class="btn btn-primary ms-2" onclick="exportToExcel()">
                        <i class="fas fa-file-excel me-2"></i>Export as Excel
                    </button>
                    <button class="btn btn-secondary ms-2" onclick="window.print()">
                        <i class="fas fa-print me-2"></i>Print Report
                    </button>
                </div>
                
                <!-- Sales Statistics -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3><?php echo $sales_stats['total_orders'] ?? 0; ?></h3>
                        <p>Total Orders</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo formatCurrency($sales_stats['total_revenue'] ?? 0); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo formatCurrency($sales_stats['avg_order_value'] ?? 0); ?></h3>
                        <p>Average Order Value</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $sales_stats['unique_customers'] ?? 0; ?></h3>
                        <p>Unique Customers</p>
                    </div>
                </div>
                
                <!-- Daily Sales Chart -->
                <div class="chart-container">
                    <h4>Daily Sales Trend</h4>
                    <canvas id="salesChart" height="100"></canvas>
                </div>
                
                <!-- Top Products -->
                <div class="table-container">
                    <h4>Top Selling Products</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Category</th>
                                    <th>Quantity Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_products as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                                    <td><?php echo $product['category_name'] ?? 'Uncategorized'; ?></td>
                                    <td><?php echo $product['total_sold']; ?></td>
                                    <td><?php echo formatCurrency($product['total_revenue']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Top Customers -->
                <div class="table-container">
                    <h4>Top Customers</h4>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Email</th>
                                    <th>Orders</th>
                                    <th>Total Spent</th>
                                    <th>Last Order</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_customers as $customer): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($customer['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                    <td><?php echo $customer['order_count']; ?></td>
                                    <td><?php echo formatCurrency($customer['total_spent']); ?></td>
                                    <td><?php echo $customer['last_order'] ? date('d/m/Y', strtotime($customer['last_order'])) : 'N/A'; ?></td>
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
    
    <script>
        // Prepare data for chart
        const dates = <?php echo json_encode(array_column($daily_sales, 'date')); ?>;
        const revenues = <?php echo json_encode(array_column($daily_sales, 'daily_revenue')); ?>;
        const orders = <?php echo json_encode(array_column($daily_sales, 'order_count')); ?>;
        
        // Create sales chart
        const ctx = document.getElementById('salesChart').getContext('2d');
        const salesChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dates,
                datasets: [{
                    label: 'Revenue (Rp)',
                    data: revenues,
                    borderColor: '#0b3b2e',
                    backgroundColor: 'rgba(11, 59, 46, 0.1)',
                    borderWidth: 2,
                    tension: 0.1
                }, {
                    label: 'Orders',
                    data: orders,
                    borderColor: '#d4af37',
                    backgroundColor: 'rgba(212, 175, 55, 0.1)',
                    borderWidth: 2,
                    tension: 0.1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: 'Revenue (Rp)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'Orders'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
        
        function exportToPDF() {
            alert('PDF export functionality would be implemented here.');
        }
        
        function exportToExcel() {
            alert('Excel export functionality would be implemented here.');
        }
    </script>
</body>
</html>