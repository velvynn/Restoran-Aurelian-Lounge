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

// Get user data
$db->query('SELECT * FROM users WHERE id = :id');
$db->bind(':id', $user_id);
$user = $db->single();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone']);
        $address = sanitize($_POST['address']);
        
        // Update user data
        $db->query('UPDATE users SET full_name = :full_name, email = :email, phone = :phone, address = :address WHERE id = :id');
        $db->bind(':full_name', $full_name);
        $db->bind(':email', $email);
        $db->bind(':phone', $phone);
        $db->bind(':address', $address);
        $db->bind(':id', $user_id);
        
        if ($db->execute()) {
            $_SESSION['success'] = 'Profile updated successfully!';
            $_SESSION['full_name'] = $full_name; // Update session
            header('Location: profile.php');
            exit();
        } else {
            $_SESSION['error'] = 'Failed to update profile.';
        }
    }
    
    // Handle password change
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $db->query('UPDATE users SET password = :password WHERE id = :id');
                    $db->bind(':password', $hashed_password);
                    $db->bind(':id', $user_id);
                    
                    if ($db->execute()) {
                        $_SESSION['success'] = 'Password changed successfully!';
                    } else {
                        $_SESSION['error'] = 'Failed to change password.';
                    }
                } else {
                    $_SESSION['error'] = 'Password must be at least 6 characters.';
                }
            } else {
                $_SESSION['error'] = 'New passwords do not match.';
            }
        } else {
            $_SESSION['error'] = 'Current password is incorrect.';
        }
        header('Location: profile.php');
        exit();
    }
    
    // Handle profile photo upload
    if (isset($_POST['upload_photo']) && isset($_FILES['profile_photo'])) {
        $upload_dir = '../uploads/profile_photos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['profile_photo']['name']);
        $target_file = $upload_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        
        // Check if image file is actual image
        $check = getimagesize($_FILES['profile_photo']['tmp_name']);
        if ($check !== false) {
            // Check file size (max 2MB)
            if ($_FILES['profile_photo']['size'] <= 2000000) {
                // Allow certain file formats
                if ($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg" || $imageFileType == "gif") {
                    if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_file)) {
                        // Update user record with photo path
                        $db->query('UPDATE users SET profile_photo = :photo WHERE id = :id');
                        $db->bind(':photo', $file_name);
                        $db->bind(':id', $user_id);
                        
                        if ($db->execute()) {
                            $_SESSION['success'] = 'Profile photo updated successfully!';
                        } else {
                            $_SESSION['error'] = 'Failed to update profile photo.';
                        }
                    } else {
                        $_SESSION['error'] = 'Sorry, there was an error uploading your file.';
                    }
                } else {
                    $_SESSION['error'] = 'Sorry, only JPG, JPEG, PNG & GIF files are allowed.';
                }
            } else {
                $_SESSION['error'] = 'Sorry, your file is too large (max 2MB).';
            }
        } else {
            $_SESSION['error'] = 'File is not an image.';
        }
        header('Location: profile.php');
        exit();
    }
}

// Get user stats
$db->query('SELECT COUNT(*) as order_count FROM orders WHERE user_id = :user_id');
$db->bind(':user_id', $user_id);
$order_count = $db->single()['order_count'];

$db->query('SELECT SUM(total_amount) as total_spent FROM orders WHERE user_id = :user_id AND status = "completed"');
$db->bind(':user_id', $user_id);
$total_spent = $db->single()['total_spent'] ?? 0;

// Get wishlist items
$db->query('SELECT w.*, p.name, p.price, p.image, p.description 
            FROM wishlist w 
            JOIN products p ON w.product_id = p.id 
            WHERE w.user_id = :user_id 
            ORDER BY w.created_at DESC');
$db->bind(':user_id', $user_id);
$wishlist_items = $db->resultSet();

// Get recent orders
$db->query('SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 5');
$db->bind(':user_id', $user_id);
$recent_orders = $db->resultSet();

$page_title = 'My Profile';
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
        
        /* Profile Header */
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
            text-align: center;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: linear-gradient(135deg, #d4af37, #ffd700);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            border: 5px solid white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            margin: 0 auto 25px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .profile-avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 15px 40px rgba(212, 175, 55, 0.4);
        }
        
        .profile-avatar-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .avatar-initials {
            font-size: 3rem;
            font-weight: 700;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }
        
        .avatar-upload-btn {
            position: absolute;
            bottom: 10px;
            right: 10px;
            background: var(--accent-color);
            color: var(--primary-color);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 3px solid white;
            z-index: 10;
        }
        
        .avatar-upload-btn:hover {
            background: #ffd700;
            transform: scale(1.1);
        }
        
        /* Profile Cards */
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            border: 1px solid #e9ecef;
            transition: transform 0.3s;
        }
        
        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
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
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            transition: all 0.3s;
            border: 1px solid #dee2e6;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            background: rgba(11, 59, 46, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: var(--primary-color);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        /* Form Styles */
        .form-label {
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s;
            font-size: 0.95rem;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }
        
        .btn-save {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(11, 59, 46, 0.2);
        }
        
        .btn-edit {
            background: var(--accent-color);
            color: var(--primary-color);
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-edit:hover {
            background: #ffd700;
            transform: translateY(-2px);
        }
        
        /* Wishlist Items */
        .wishlist-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border: 1px solid #e9ecef;
            border-radius: 12px;
            margin-bottom: 15px;
            background: white;
            transition: all 0.3s;
        }
        
        .wishlist-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .wishlist-img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            margin-right: 20px;
        }
        
        .wishlist-info {
            flex: 1;
        }
        
        .wishlist-name {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 5px;
            font-size: 1.1rem;
        }
        
        .wishlist-price {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.2rem;
        }
        
        .btn-remove-wishlist {
            background: #f8f9fa;
            color: #dc3545;
            border: 1px solid #dc3545;
            padding: 8px 15px;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-remove-wishlist:hover {
            background: #dc3545;
            color: white;
            transform: translateY(-2px);
        }
        
        /* Recent Activity */
        .activity-item {
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            background: #f8f9fa;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary-color);
        }
        
        .activity-content h6 {
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .activity-content p {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0;
        }
        
        /* Settings Tabs */
        .settings-tabs {
            display: flex;
            border-bottom: 2px solid #f0f0f0;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .settings-tab {
            padding: 12px 25px;
            background: none;
            border: none;
            font-weight: 600;
            color: #6c757d;
            position: relative;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .settings-tab:hover {
            color: var(--primary-color);
        }
        
        .settings-tab.active {
            color: var(--primary-color);
        }
        
        .settings-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: var(--accent-color);
            border-radius: 3px 3px 0 0;
        }
        
        .settings-content {
            display: none;
        }
        
        .settings-content.active {
            display: block;
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
        
        /* Modal Styles */
        .modal-content {
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 15px 15px 0 0;
            border: none;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .profile-header {
                padding: 40px 0;
                margin-bottom: 30px;
            }
            
            .profile-avatar {
                width: 120px;
                height: 120px;
            }
            
            .avatar-initials {
                font-size: 2.5rem;
            }
            
            .profile-card {
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .wishlist-item {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .wishlist-img {
                margin-right: 0;
                width: 150px;
                height: 150px;
            }
            
            .settings-tabs {
                flex-direction: column;
            }
            
            .settings-tab {
                text-align: left;
                border-bottom: 1px solid #f0f0f0;
            }
        }
        
        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
            }
            
            .avatar-initials {
                font-size: 2rem;
            }
            
            .btn-save {
                width: 100%;
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
                    <li class="nav-item">
                        <a class="nav-link" href="wishlist.php">
                            <i class="fas fa-heart"></i>
                            <?php if (count($wishlist_items) > 0): ?>
                                <span class="cart-count"><?php echo count($wishlist_items); ?></span>
                            <?php endif; ?>
                        </a>
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
                        <a class="nav-link dropdown-toggle active" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i> <?php echo $_SESSION['full_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><a class="dropdown-item" href="wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
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
    
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="container">
            <!-- Profile Avatar with Upload -->
            <div class="profile-avatar" data-bs-toggle="modal" data-bs-target="#uploadPhotoModal">
                <?php if (!empty($user['profile_photo'])): ?>
                    <img src="../uploads/profile_photos/<?php echo $user['profile_photo']; ?>" 
                         alt="Profile Photo" class="profile-avatar-img">
                <?php else: ?>
                    <div class="avatar-initials">
                        <?php 
                        $names = explode(' ', $user['full_name']);
                        $initials = '';
                        foreach ($names as $name) {
                            $initials .= strtoupper(substr($name, 0, 1));
                            if (strlen($initials) >= 2) break;
                        }
                        echo $initials ?: 'JD';
                        ?>
                    </div>
                <?php endif; ?>
                <div class="avatar-upload-btn" title="Upload Photo">
                    <i class="fas fa-camera"></i>
                </div>
            </div>
            
            <h1 class="mb-3"><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p class="lead mb-0">
                <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($user['email']); ?>
                <span class="mx-3">|</span>
                <i class="fas fa-user-tag me-2"></i><?php echo ucfirst($user['role']); ?> Member
            </p>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="container py-4">
        <div class="row">
            <!-- Left Column: Stats & Quick Links -->
            <div class="col-lg-4 mb-4">
                <!-- User Stats -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                        <div class="stat-value"><?php echo $order_count; ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <div class="stat-value"><?php echo formatCurrency($total_spent); ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <div class="stat-value"><?php echo ($order_count * 10); ?></div>
                        <div class="stat-label">Loyalty Points</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <div class="stat-value"><?php echo count($wishlist_items); ?></div>
                        <div class="stat-label">Wishlist Items</div>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="profile-card">
                    <h3 class="section-title"><i class="fas fa-bolt"></i> Quick Links</h3>
                    <div class="list-group list-group-flush">
                        <a href="orders.php" class="list-group-item list-group-item-action border-0 py-3">
                            <i class="fas fa-shopping-bag me-3"></i> My Orders
                        </a>
                        <a href="wishlist.php" class="list-group-item list-group-item-action border-0 py-3">
                            <i class="fas fa-heart me-3"></i> My Wishlist
                        </a>
                        <a href="reservation.php" class="list-group-item list-group-item-action border-0 py-3">
                            <i class="fas fa-calendar-check me-3"></i> Reservations
                        </a>
                        <a href="#" class="list-group-item list-group-item-action border-0 py-3" data-bs-toggle="modal" data-bs-target="#settingsModal">
                            <i class="fas fa-cog me-3"></i> Settings
                        </a>
                        <a href="../logout.php" class="list-group-item list-group-item-action border-0 py-3 text-danger">
                            <i class="fas fa-sign-out-alt me-3"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Right Column: Profile Information & Activities -->
            <div class="col-lg-8">
                <!-- Profile Information -->
                <div class="profile-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="section-title mb-0"><i class="fas fa-user"></i> Personal Information</h3>
                        <button class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="fas fa-edit me-2"></i> Edit Profile
                        </button>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Full Name</label>
                            <div class="form-control bg-light"><?php echo htmlspecialchars($user['full_name']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email Address</label>
                            <div class="form-control bg-light"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Phone Number</label>
                            <div class="form-control bg-light"><?php echo htmlspecialchars($user['phone'] ?? 'Not set'); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Address</label>
                            <div class="form-control bg-light"><?php echo htmlspecialchars($user['address'] ?? 'Not set'); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Member Since</label>
                            <div class="form-control bg-light"><?php echo date('F d, Y', strtotime($user['created_at'])); ?></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Account Type</label>
                            <div class="form-control bg-light"><?php echo ucfirst($user['role']); ?> Member</div>
                        </div>
                    </div>
                </div>
                
                <!-- Wishlist Preview -->
                <div class="profile-card">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="section-title mb-0"><i class="fas fa-heart"></i> My Wishlist</h3>
                        <a href="wishlist.php" class="btn btn-edit">
                            <i class="fas fa-eye me-2"></i> View All
                        </a>
                    </div>
                    
                    <?php if (!empty($wishlist_items)): ?>
                        <div class="row">
                            <?php 
                            $preview_items = array_slice($wishlist_items, 0, 2);
                            foreach ($preview_items as $item): 
                            ?>
                            <div class="col-md-6 mb-3">
                                <div class="wishlist-item">
                                    <img src="<?php echo getProductImage($item['image']); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="wishlist-img">
                                    <div class="wishlist-info">
                                        <div class="wishlist-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                        <div class="wishlist-price"><?php echo formatCurrency($item['price']); ?></div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-heart fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">Your wishlist is empty</p>
                            <a href="menu.php" class="btn btn-edit mt-3">Browse Menu</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Activity -->
                <div class="profile-card">
                    <h3 class="section-title"><i class="fas fa-history"></i> Recent Activity</h3>
                    
                    <?php if (!empty($recent_orders)): ?>
                        <?php foreach ($recent_orders as $order): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="activity-content">
                                <h6 class="mb-1">Order #<?php echo $order['order_code']; ?></h6>
                                <p class="mb-1"><?php echo date('F j, Y - g:i A', strtotime($order['created_at'])); ?></p>
                                <p class="mb-0">
                                    <span class="badge bg-<?php 
                                        echo match($order['status']) {
                                            'completed' => 'success',
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                    <span class="ms-2"><?php echo formatCurrency($order['total_amount']); ?></span>
                                </p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No recent activity</p>
                            <a href="menu.php" class="btn btn-edit">Place Your First Order</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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
    
    <!-- Upload Photo Modal -->
    <div class="modal fade" id="uploadPhotoModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-camera me-2"></i> Upload Profile Photo</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Choose Photo</label>
                            <input type="file" class="form-control" name="profile_photo" accept="image/*" required>
                            <div class="form-text">Max size: 2MB. Allowed: JPG, PNG, GIF</div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            For best results, use a square photo (1:1 ratio)
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="upload_photo" class="btn btn-save">Upload Photo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Edit Profile</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
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
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" 
                                       value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="2"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_profile" class="btn btn-save">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Settings Modal -->
    <div class="modal fade" id="settingsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-cog me-2"></i> Account Settings</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <!-- Settings Tabs -->
                    <div class="settings-tabs">
                        <button type="button" class="settings-tab active" data-tab="password">Password</button>
                        <button type="button" class="settings-tab" data-tab="notifications">Notifications</button>
                        <button type="button" class="settings-tab" data-tab="privacy">Privacy</button>
                    </div>
                    
                    <!-- Password Settings -->
                    <div class="settings-content active" id="passwordTab">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Current Password *</label>
                                    <input type="password" class="form-control" name="current_password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">New Password *</label>
                                    <input type="password" class="form-control" name="new_password" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Confirm New Password *</label>
                                    <input type="password" class="form-control" name="confirm_password" required>
                                </div>
                            </div>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Password must be at least 6 characters long
                            </div>
                            <div class="text-end">
                                <button type="submit" name="change_password" class="btn btn-save">
                                    <i class="fas fa-key me-2"></i> Change Password
                                </button>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Notification Settings -->
                    <div class="settings-content" id="notificationsTab">
                        <div class="mb-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                                <label class="form-check-label" for="emailNotifications">
                                    Email notifications for new orders
                                </label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="promoEmails" checked>
                                <label class="form-check-label" for="promoEmails">
                                    Promotional emails and offers
                                </label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="smsNotifications">
                                <label class="form-check-label" for="smsNotifications">
                                    SMS notifications for order updates
                                </label>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-save" id="saveNotifications">
                                <i class="fas fa-bell me-2"></i> Save Settings
                            </button>
                        </div>
                    </div>
                    
                    <!-- Privacy Settings -->
                    <div class="settings-content" id="privacyTab">
                        <div class="mb-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="showProfile" checked>
                                <label class="form-check-label" for="showProfile">
                                    Show my profile to other users
                                </label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="activityStatus" checked>
                                <label class="form-check-label" for="activityStatus">
                                    Show my activity status
                                </label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="dataCollection" checked>
                                <label class="form-check-label" for="dataCollection">
                                    Allow data collection for better experience
                                </label>
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-shield-alt me-2"></i>
                            We respect your privacy. Your data is protected by our privacy policy.
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-save" id="savePrivacy">
                                <i class="fas fa-shield-alt me-2"></i> Save Privacy Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Settings Tabs
        document.querySelectorAll('.settings-tab').forEach(tab => {
            tab.addEventListener('click', function() {
                const tabId = this.dataset.tab;
                
                // Update active tab
                document.querySelectorAll('.settings-tab').forEach(t => {
                    t.classList.remove('active');
                });
                this.classList.add('active');
                
                // Show active content
                document.querySelectorAll('.settings-content').forEach(content => {
                    content.classList.remove('active');
                });
                document.getElementById(tabId + 'Tab').classList.add('active');
            });
        });
        
        // Save notification settings
        document.getElementById('saveNotifications').addEventListener('click', function() {
            const emailNotif = document.getElementById('emailNotifications').checked;
            const promoEmails = document.getElementById('promoEmails').checked;
            const smsNotif = document.getElementById('smsNotifications').checked;
            
            // Save to localStorage (in real app, this would be AJAX to server)
            localStorage.setItem('notificationSettings', JSON.stringify({
                email: emailNotif,
                promo: promoEmails,
                sms: smsNotif
            }));
            
            alert('Notification settings saved!');
        });
        
        // Save privacy settings
        document.getElementById('savePrivacy').addEventListener('click', function() {
            const showProfile = document.getElementById('showProfile').checked;
            const activityStatus = document.getElementById('activityStatus').checked;
            const dataCollection = document.getElementById('dataCollection').checked;
            
            // Save to localStorage (in real app, this would be AJAX to server)
            localStorage.setItem('privacySettings', JSON.stringify({
                profile: showProfile,
                activity: activityStatus,
                data: dataCollection
            }));
            
            alert('Privacy settings saved!');
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Load saved settings
        document.addEventListener('DOMContentLoaded', function() {
            // Load notification settings
            const notifSettings = localStorage.getItem('notificationSettings');
            if (notifSettings) {
                const settings = JSON.parse(notifSettings);
                document.getElementById('emailNotifications').checked = settings.email;
                document.getElementById('promoEmails').checked = settings.promo;
                document.getElementById('smsNotifications').checked = settings.sms;
            }
            
            // Load privacy settings
            const privacySettings = localStorage.getItem('privacySettings');
            if (privacySettings) {
                const settings = JSON.parse(privacySettings);
                document.getElementById('showProfile').checked = settings.profile;
                document.getElementById('activityStatus').checked = settings.activity;
                document.getElementById('dataCollection').checked = settings.data;
            }
        });
        
        // Preview image before upload
        document.querySelector('input[name="profile_photo"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    // You could show a preview here if needed
                    console.log('File selected:', file.name);
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>