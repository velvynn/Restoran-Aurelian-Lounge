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

// Handle add to cart via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_cart'])) {
        $product_id = intval($_POST['product_id']);
        $quantity = intval($_POST['quantity']);
        
        // Cek apakah produk sudah ada di keranjang
        $db->query('SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id');
        $db->bind(':user_id', $user_id);
        $db->bind(':product_id', $product_id);
        $existing_item = $db->single();
        
        if ($existing_item) {
            // Update quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            $db->query('UPDATE cart SET quantity = :quantity WHERE id = :id');
            $db->bind(':quantity', $new_quantity);
            $db->bind(':id', $existing_item['id']);
            $db->execute();
        } else {
            // Tambah ke keranjang
            $db->query('INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)');
            $db->bind(':user_id', $user_id);
            $db->bind(':product_id', $product_id);
            $db->bind(':quantity', $quantity);
            $db->execute();
        }
        
        $_SESSION['success'] = 'Product added to cart!';
        header('Location: ' . $_SERVER['PHP_SELF'] . (isset($_GET['category']) ? '?category=' . $_GET['category'] : ''));
        exit();
    }
}

// Ambil semua kategori
$db->query('SELECT * FROM categories WHERE id IN (SELECT DISTINCT category_id FROM products WHERE is_active = 1) ORDER BY name');
$categories = $db->resultSet();

// Ambil semua produk aktif
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

if ($category_filter > 0) {
    $db->query('SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1 AND p.category_id = :category_id 
                ORDER BY p.name');
    $db->bind(':category_id', $category_filter);
} else {
    $db->query('SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                WHERE p.is_active = 1 
                ORDER BY p.name');
}

$products = $db->resultSet();

// Ambil jumlah produk per kategori
$db->query('SELECT c.id, c.name, COUNT(p.id) as product_count 
            FROM categories c 
            LEFT JOIN products p ON c.id = p.category_id AND p.is_active = 1 
            GROUP BY c.id 
            ORDER BY c.name');
$category_counts = $db->resultSet();

// Ambil jumlah item di keranjang
$cart_count = getCartCount();

$page_title = 'Our Menu';
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
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0b3b2e;
            --secondary-color: #0f5132;
            --accent-color: #d4af37;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--primary-color) !important;
        }
        
        .nav-link {
            font-weight: 500;
        }
        
        .nav-link.active {
            color: var(--accent-color) !important;
            font-weight: 600;
        }
        
        .hero-section {
            background: linear-gradient(rgba(11, 59, 46, 0.9), rgba(11, 59, 46, 0.9)), 
                        url('https://images.unsplash.com/photo-1414235077428-338989a2e8c0?ixlib=rb-4.0.3&auto=format&fit=crop&w=1350&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 80px 0;
            text-align: center;
        }
        
        .hero-section h1 {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 20px;
        }
        
        .hero-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .category-filter {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin: -40px auto 40px;
            position: relative;
            max-width: 1200px;
        }
        
        .category-btn {
            border: 2px solid #e9ecef;
            background: white;
            color: #495057;
            padding: 8px 18px;
            border-radius: 50px;
            margin: 5px;
            font-weight: 500;
            font-size: 0.95rem;
            transition: all 0.3s;
        }
        
        .category-btn:hover,
        .category-btn.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
        
        /* Product Card */
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            margin-bottom: 25px;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
            border: 1px solid #f0f0f0;
        }
        
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.15);
        }
        
        .product-img {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .product-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .product-card:hover .product-img img {
            transform: scale(1.08);
        }
        
        .product-content {
            padding: 18px;
        }
        
        .product-category {
            color: var(--accent-color);
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: inline-block;
            background: rgba(212, 175, 55, 0.1);
            padding: 4px 10px;
            border-radius: 4px;
        }
        
        .product-title {
            font-size: 1.15rem;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--primary-color);
            line-height: 1.4;
            height: 2.8em;
            overflow: hidden;
            position: relative;
        }

        .product-title:after {
            content: '...';
            position: absolute;
            bottom: 0;
            right: 0;
            padding-left: 10px;
            background: linear-gradient(to right, transparent, white 20%);
        }
        
        .product-description {
            color: #6c757d;
            font-size: 0.88rem;
            margin-bottom: 12px;
            line-height: 1.5;
            height: 4.5em;
            overflow: hidden;
            position: relative;
        }

        .product-description:after {
            content: '...';
            position: absolute;
            bottom: 0;
            right: 0;
            padding-left: 10px;
            background: linear-gradient(to right, transparent, white 20%);
        }
        
        .product-price-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 12px;
            padding-top: 12px;
            border-top: 1px solid #e9ecef;
        }
        
        .price-container {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        
        .price-amount {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1.2;
        }
        
        .original-price {
            font-size: 0.85rem;
            color: #6c757d;
            text-decoration: line-through;
            font-weight: 500;
        }
        
        .btn-add-to-cart {
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 7px 16px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        
        .btn-add-to-cart:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(11, 59, 46, 0.2);
        }
        
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }
        
        .quantity-btn {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 0.9rem;
        }
        
        .quantity-btn:hover {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .quantity-input {
            width: 45px;
            text-align: center;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 5px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .discount-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: linear-gradient(135deg, var(--accent-color), #ffd700);
            color: var(--primary-color);
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 0.8rem;
            z-index: 1;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        .stock-info {
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 12px;
            display: inline-block;
            margin-top: 5px;
        }
        
        .stock-low {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-ok {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }
        
        .empty-state i {
            font-size: 70px;
            color: #dee2e6;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            color: #6c757d;
            margin-bottom: 10px;
            font-size: 1.2rem;
        }
        
        .cart-badge {
            position: relative;
            display: inline-block;
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            font-size: 0.7rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        footer {
            background: var(--primary-color);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
        
        .footer-links a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            margin-right: 15px;
            font-size: 0.9rem;
        }
        
        .footer-links a:hover {
            color: white;
        }
        
        .social-icons a {
            color: white;
            font-size: 1.1rem;
            margin-right: 12px;
            transition: all 0.3s;
        }
        
        .social-icons a:hover {
            color: var(--accent-color);
            transform: translateY(-3px);
        }
        
        .alert-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }
        
        @media (min-width: 1200px) {
            .product-card {
                margin-bottom: 30px;
            }
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 60px 0;
            }
            
            .hero-section h1 {
                font-size: 2.2rem;
            }
            
            .hero-section p {
                font-size: 1rem;
            }
            
            .category-filter {
                margin: -30px auto 30px;
                padding: 15px;
            }
            
            .category-btn {
                padding: 6px 14px;
                font-size: 0.9rem;
                margin: 3px;
            }
            
            .product-img {
                height: 180px;
            }
            
            .product-title {
                font-size: 1.1rem;
            }
            
            .product-description {
                font-size: 0.85rem;
            }
            
            .price-amount {
                font-size: 1.15rem;
            }
        }
        
        @media (max-width: 576px) {
            .product-content {
                padding: 15px;
            }
            
            .product-price-section {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .btn-add-to-cart {
                width: 100%;
                justify-content: center;
            }
            
            .quantity-controls {
                width: 100%;
                justify-content: center;
            }
        }
        
        .product-rating {
            margin-bottom: 8px;
        }
        
        .rating-stars {
            color: #ffc107;
            font-size: 0.85rem;
        }
        
        .rating-count {
            color: #6c757d;
            font-size: 0.8rem;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
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
                        <a class="nav-link active" href="menu.php">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link cart-badge" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <?php if ($cart_count > 0): ?>
                                <span class="cart-count"><?php echo $cart_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i> <?php echo $_SESSION['full_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="orders.php"><i class="fas fa-shopping-bag me-2"></i>My Orders</a></li>
                            <li><hr class="dropdown-divider"></li>
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
                <?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </div>
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1>Our Delicious Menu</h1>
            <p>Discover our wide selection of premium dishes crafted with love and fresh ingredients</p>
        </div>
    </section>
    
    <!-- Category Filter -->
    <div class="container">
        <div class="category-filter">
            <h5 class="text-center mb-4" style="font-size: 1.1rem;">Filter by Category</h5>
            <div class="text-center">
                <a href="menu.php" class="btn category-btn <?php echo $category_filter == 0 ? 'active' : ''; ?>">
                    All Products <span class="badge bg-secondary"><?php echo count($products); ?></span>
                </a>
                <?php foreach ($category_counts as $cat): ?>
                    <?php if ($cat['product_count'] > 0): ?>
                    <a href="menu.php?category=<?php echo $cat['id']; ?>" 
                       class="btn category-btn <?php echo $category_filter == $cat['id'] ? 'active' : ''; ?>">
                        <?php echo $cat['name']; ?> <span class="badge bg-secondary"><?php echo $cat['product_count']; ?></span>
                    </a>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    
    <!-- Products Grid -->
    <section class="py-4">
        <div class="container">
            <div class="row">
                <?php if (!empty($products)): ?>
                    <?php 
                    // DEBUG: Tampilkan informasi produk untuk troubleshooting
                    // echo '<pre>';
                    // print_r($products);
                    // echo '</pre>';
                    ?>
                    
                    <?php foreach ($products as $product): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="product-card">
                                <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                                    <?php 
                                    $discount = round((($product['original_price'] - $product['price']) / $product['original_price']) * 100);
                                    ?>
                                    <div class="discount-badge">Save <?php echo $discount; ?>%</div>
                                <?php endif; ?>
                                
                                <div class="product-img">
                                    <?php 
                                    // PERBAIKAN: Gunakan fungsi getProductImage() yang sudah diperbaiki
                                    $image_path = getProductImage($product['image']);
                                    ?>
                                    <img src="<?php echo $image_path; ?>" 
                                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                                         onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>assets/img/default-product.jpg';"
                                         style="width: 100%; height: 100%; object-fit: cover;">
                                    
                                    <!-- DEBUG: Uncomment untuk troubleshooting gambar -->
                                    <!-- <div style="position: absolute; top: 5px; left: 5px; background: rgba(0,0,0,0.7); color: white; padding: 2px 5px; font-size: 10px; border-radius: 3px;">
                                        <?php echo htmlspecialchars($product['image']); ?>
                                    </div> -->
                                </div>
                                
                                <div class="product-content">
                                    <div class="product-category"><?php echo $product['category_name'] ?? 'Uncategorized'; ?></div>
                                    <h3 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    
                                    <!-- Optional: Rating Stars -->
                                    <div class="product-rating">
                                        <span class="rating-stars">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                        </span>
                                        <span class="rating-count">(4.5)</span>
                                    </div>
                                    
                                    <p class="product-description">
                                        <?php echo substr(htmlspecialchars($product['description']), 0, 120); ?>
                                        <?php if (strlen($product['description']) > 120): ?>...<?php endif; ?>
                                    </p>
                                    
                                    <!-- Stock info -->
                                    <?php if ($product['stock'] < 10): ?>
                                        <div class="stock-info stock-low">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Hampir habis: <?php echo $product['stock']; ?> tersisa
                                        </div>
                                    <?php elseif ($product['stock'] < 30): ?>
                                        <div class="stock-info stock-ok">
                                            <i class="fas fa-box me-1"></i>
                                            Tersedia: <?php echo $product['stock']; ?> pcs
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="product-price-section">
                                        <div class="price-container">
                                            <?php if ($product['original_price'] && $product['original_price'] > $product['price']): ?>
                                                <span class="original-price"><?php echo formatCurrency($product['original_price']); ?></span>
                                            <?php endif; ?>
                                            <span class="price-amount"><?php echo formatCurrency($product['price']); ?></span>
                                        </div>
                                        
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="add_to_cart" value="1">
                                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                            
                                            <div class="quantity-controls">
                                                <button type="button" class="quantity-btn minus" data-id="<?php echo $product['id']; ?>">-</button>
                                                <input type="number" class="quantity-input" name="quantity" 
                                                       id="quantity-<?php echo $product['id']; ?>" value="1" min="1" max="<?php echo min(10, $product['stock']); ?>">
                                                <button type="button" class="quantity-btn plus" data-id="<?php echo $product['id']; ?>">+</button>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-add-to-cart mt-2">
                                                <i class="fas fa-plus me-1"></i> Add to Cart
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-utensils"></i>
                            <h4>No products found</h4>
                            <p>No products available in this category.</p>
                            <a href="menu.php" class="btn btn-primary btn-sm">View All Products</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5 style="font-size: 1.1rem;">Aurelian Restaurant</h5>
                    <p style="font-size: 0.9rem;">Fine dining experience with premium cuisine.</p>
                    <div class="footer-links mt-2">
                        <a href="index.php">Home</a>
                        <a href="menu.php">Menu</a>
                        <a href="#">About Us</a>
                        <a href="#">Contact</a>
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <h5 style="font-size: 1.1rem;">Follow Us</h5>
                    <div class="social-icons mt-2">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                    <p class="mt-2" style="font-size: 0.85rem;">&copy; 2024 Aurelian Restaurant. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Quantity controls
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const productId = this.dataset.id;
                const input = document.getElementById(`quantity-${productId}`);
                let quantity = parseInt(input.value);
                
                if (this.classList.contains('plus')) {
                    quantity = Math.min(parseInt(input.max), quantity + 1);
                } else if (this.classList.contains('minus')) {
                    quantity = Math.max(parseInt(input.min), quantity - 1);
                }
                
                input.value = quantity;
            });
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Form validation untuk quantity
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                let value = parseInt(this.value);
                const min = parseInt(this.min);
                const max = parseInt(this.max);
                
                if (isNaN(value) || value < min) {
                    this.value = min;
                } else if (value > max) {
                    this.value = max;
                }
            });
            
            input.addEventListener('blur', function() {
                if (this.value === '') {
                    this.value = this.min;
                }
            });
        });
        
        // Debug: Log semua gambar untuk troubleshooting
        console.log("=== PRODUCT IMAGES DEBUG ===");
        <?php foreach ($products as $product): ?>
        console.log("Product: <?php echo $product['name']; ?>");
        console.log("  Image from DB: <?php echo $product['image']; ?>");
        console.log("  Image URL: <?php echo getProductImage($product['image']); ?>");
        <?php endforeach; ?>
        console.log("=== END DEBUG ===");
    </script>
</body>
</html>

<?php
// TAMBAHKAN FUNGSI INI DI BAWAH FILE UNTUK MENGATASI MASALAH GAMBAR
// Fungsi alternatif untuk mendapatkan path gambar yang lebih reliable
function getProductImageDirect($image, $product_name = '') {
    // Default image
    $default_image = SITE_URL . 'assets/img/default-product.jpg';
    
    // Jika image kosong, return default
    if (empty($image) || trim($image) == '') {
        return $default_image;
    }
    
    $image = trim($image);
    
    // Array kemungkinan lokasi gambar
    $possible_paths = [
        // Path absolut dari server
        $_SERVER['DOCUMENT_ROOT'] . '/restaurant-aurelian/assets/img/' . $image,
        $_SERVER['DOCUMENT_ROOT'] . '/assets/img/' . $image,
        
        // Path relatif dari file ini
        dirname(__FILE__) . '/../assets/img/' . $image,
        dirname(dirname(__FILE__)) . '/assets/img/' . $image,
        
        // Path untuk web
        'assets/img/' . $image,
        '../assets/img/' . $image,
        '../../assets/img/' . $image,
        SITE_URL . 'assets/img/' . $image
    ];
    
    // Cek apakah file ada di sistem
    foreach ($possible_paths as $local_path) {
        if (file_exists($local_path)) {
            // Jika ditemukan di filesystem, return URL yang tepat
            if (strpos($local_path, $_SERVER['DOCUMENT_ROOT']) !== false) {
                // Konversi path lokal ke URL
                $web_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $local_path);
                return SITE_URL . ltrim($web_path, '/');
            }
            // Jika sudah berupa path web, return langsung
            return $local_path;
        }
    }
    
    // Coba URL langsung
    $direct_url = SITE_URL . 'assets/img/' . $image;
    
    // Debug: tampilkan informasi troubleshooting
    error_log("Product Image Debug: " . $product_name);
    error_log("Image from DB: " . $image);
    error_log("Trying URL: " . $direct_url);
    
    return $direct_url;
}

// Fungsi untuk mengecek apakah gambar ada di server
function checkImageExists($image_path) {
    // Coba cek dengan file_exists untuk path lokal
    if (file_exists($image_path)) {
        return true;
    }
    
    // Coba cek dengan get_headers untuk URL
    $headers = @get_headers($image_path);
    if ($headers && strpos($headers[0], '200')) {
        return true;
    }
    
    return false;
}
?>