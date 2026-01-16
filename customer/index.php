<?php
session_start();

// Debug
error_log("=== CUSTOMER INDEX ACCESSED ===");
error_log("Session: " . print_r($_SESSION, true));

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

error_log("Customer access granted for user: " . $_SESSION['username']);

$page_title = 'Customer Dashboard';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Aurelian Restaurant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Global Styles */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-brand {
            font-weight: bold;
            color: #0b3b2e;
        }
        
        /* Welcome Section - UPDATED & IMPROVED */
        .welcome-section {
            background: linear-gradient(135deg, #0b3b2e 0%, #0f5132 100%);
            color: white;
            padding: 80px 0;
            margin-top: 20px;
            border-radius: 20px;
            overflow: hidden;
            position: relative;
            box-shadow: 0 15px 30px rgba(11, 59, 46, 0.2);
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: 
                radial-gradient(circle at 80% 20%, rgba(212, 175, 55, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 20% 80%, rgba(255, 255, 255, 0.08) 0%, transparent 50%);
            z-index: 1;
        }
        
        .welcome-content-wrapper {
            position: relative;
            z-index: 2;
        }
        
        /* Profile Avatar Circle - Enhanced */
        .profile-avatar-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #d4af37 0%, #ffd700 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            border: 4px solid white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            animation: pulse 2s infinite;
            margin: 0 auto 25px;
        }
        
        .profile-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            transition: transform 0.5s ease;
        }
        
        .profile-photo:hover {
            transform: scale(1.1);
        }
        
        .avatar-initials {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0b3b2e;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 100%;
        }
        
        /* Animated Rings around Avatar */
        .avatar-ring {
            position: absolute;
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 50%;
            animation: ripple 3s infinite;
        }
        
        .avatar-ring.ring-1 {
            width: 140px;
            height: 140px;
            animation-delay: 0s;
        }
        
        .avatar-ring.ring-2 {
            width: 160px;
            height: 160px;
            animation-delay: 0.5s;
        }
        
        .avatar-ring.ring-3 {
            width: 180px;
            height: 180px;
            animation-delay: 1s;
        }
        
        /* Status Indicator */
        .avatar-status {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 25px;
            height: 25px;
            background: #4CAF50;
            border: 3px solid white;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            z-index: 3;
        }
        
        /* Welcome Text - IMPROVED LAYOUT */
        .welcome-text-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .welcome-title {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 2px 8px rgba(0,0,0,0.3);
            line-height: 1.2;
        }
        
        .welcome-role {
            color: #ffd700;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: inline-block;
            padding: 8px 20px;
            background: rgba(212, 175, 55, 0.15);
            border-radius: 20px;
            backdrop-filter: blur(5px);
        }
        
        .welcome-description {
            font-size: 1.25rem;
            line-height: 1.6;
            margin: 25px auto;
            max-width: 700px;
            opacity: 0.95;
            padding: 0 20px;
        }
        
        /* Action Buttons - IMPROVED POSITIONING */
        .welcome-actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0 40px;
            flex-wrap: wrap;
        }
        
        .welcome-actions .btn {
            padding: 15px 35px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            min-width: 180px;
        }
        
        .welcome-actions .btn-primary {
            background: linear-gradient(135deg, #d4af37 0%, #ffd700 100%);
            border: none;
            color: #0b3b2e;
        }
        
        .welcome-actions .btn-primary:hover {
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 12px 25px rgba(212, 175, 55, 0.4);
        }
        
        .welcome-actions .btn-outline-light {
            border: 2px solid rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }
        
        .welcome-actions .btn-outline-light:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: white;
            transform: translateY(-5px) scale(1.05);
            box-shadow: 0 12px 25px rgba(255, 255, 255, 0.2);
        }
        
        /* Meta Information - IMPROVED LAYOUT */
        .welcome-meta {
            padding-top: 25px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 40px;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 1rem;
            opacity: 0.95;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 12px;
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
        }
        
        .meta-item:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-3px);
        }
        
        .meta-item i {
            font-size: 1.2rem;
        }
        
        .meta-item strong {
            font-size: 1.2rem;
            margin-left: 5px;
        }
        
        /* =================================================== */
        /* PERUBAHAN DI SINI: Right Column Illustration - DIGANTI DENGAN GAMBAR MAKANAN */
        /* =================================================== */
        
        /* Container untuk gambar makanan */
        .food-image-container {
            position: relative;
            width: 100%;
            max-width: 350px;
            height: 350px;
            margin: 0 auto;
        }
        
        /* Gambar makanan dalam bentuk bulat */
        .food-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
            position: relative;
            z-index: 2;
            border: 10px solid transparent;
            background: linear-gradient(45deg, #0b3b2e, #0f5132, #d4af37, #ffd700);
            background-size: 300% 300%;
            padding: 5px;
            animation: gradientBorder 3s ease infinite, float 4s ease-in-out infinite;
            box-shadow: 
                0 0 40px rgba(212, 175, 55, 0.3),
                inset 0 0 40px rgba(0, 0, 0, 0.2);
            transition: all 0.5s ease;
        }
        
        @keyframes gradientBorder {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }
        
        .food-image:hover {
            animation-play-state: paused;
            transform: scale(1.08);
            box-shadow: 
                0 0 60px rgba(212, 175, 55, 0.5),
                inset 0 0 50px rgba(0, 0, 0, 0.3);
        }
        
        /* Floating elements around food image */
        .food-orb {
            position: absolute;
            width: 30px;
            height: 30px;
            background: linear-gradient(45deg, #d4af37, #ffd700);
            border-radius: 50%;
            animation: orbit 15s linear infinite;
            z-index: 1;
            box-shadow: 0 0 20px rgba(212, 175, 55, 0.8);
        }
        
        .food-orb:nth-child(1) {
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            animation-delay: 0s;
        }
        
        .food-orb:nth-child(2) {
            top: 50%;
            right: -15px;
            transform: translateY(-50%);
            animation-delay: 5s;
        }
        
        .food-orb:nth-child(3) {
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            animation-delay: 10s;
        }
        
        .food-orb:nth-child(4) {
            top: 50%;
            left: -15px;
            transform: translateY(-50%);
            animation-delay: 15s;
        }
        
        @keyframes orbit {
            0% {
                transform: translateX(-50%) rotate(0deg) translateX(200px) rotate(0deg);
            }
            100% {
                transform: translateX(-50%) rotate(360deg) translateX(200px) rotate(-360deg);
            }
        }
        
        /* Container untuk welcome illustration */
        .welcome-illustration {
            position: relative;
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        /* =================================================== */
        /* AKHIR PERUBAHAN */
        /* =================================================== */
        
        /* Animation Keyframes */
        @keyframes pulse {
            0% { 
                transform: scale(1); 
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            }
            50% { 
                transform: scale(1.05); 
                box-shadow: 0 15px 40px rgba(212, 175, 55, 0.4);
            }
            100% { 
                transform: scale(1); 
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            }
        }
        
        @keyframes ripple {
            0% {
                transform: scale(0.8);
                opacity: 1;
            }
            100% {
                transform: scale(1.3);
                opacity: 0;
            }
        }
        
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
            100% { transform: translateY(0px); }
        }
        
        /* Card Styles */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
        }
        
        .cart-count {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc3545;
            color: white;
            font-size: 0.7rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .welcome-illustration {
                height: 300px;
                margin-top: 30px;
            }
            
            .food-image-container {
                max-width: 280px;
                height: 280px;
            }
            
            .welcome-actions {
                gap: 15px;
            }
            
            .welcome-actions .btn {
                padding: 12px 25px;
                min-width: 160px;
            }
        }
        
        @media (max-width: 768px) {
            .welcome-title {
                font-size: 2.2rem;
            }
            
            .welcome-description {
                font-size: 1.1rem;
                padding: 0 15px;
            }
            
            .welcome-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .welcome-actions .btn {
                width: 100%;
                max-width: 300px;
            }
            
            .welcome-meta {
                flex-direction: column;
                gap: 15px;
                align-items: center;
            }
            
            .meta-item {
                width: 100%;
                max-width: 300px;
                justify-content: center;
            }
            
            .profile-avatar-circle {
                width: 100px;
                height: 100px;
            }
            
            .avatar-initials {
                font-size: 2rem;
            }
            
            .food-image-container {
                max-width: 250px;
                height: 250px;
            }
        }
        
        @media (max-width: 576px) {
            .welcome-section {
                padding: 60px 0;
                border-radius: 15px;
                margin-top: 10px;
            }
            
            .welcome-title {
                font-size: 1.8rem;
            }
            
            .welcome-role {
                font-size: 1rem;
                padding: 6px 15px;
            }
            
            .welcome-description {
                font-size: 1rem;
            }
            
            .profile-avatar-circle {
                width: 80px;
                height: 80px;
            }
            
            .avatar-initials {
                font-size: 1.5rem;
            }
            
            .avatar-ring.ring-1 {
                width: 100px;
                height: 100px;
            }
            
            .avatar-ring.ring-2 {
                width: 120px;
                height: 120px;
            }
            
            .avatar-ring.ring-3 {
                width: 140px;
                height: 140px;
            }
            
            .food-image-container {
                max-width: 200px;
                height: 200px;
            }
            
            .food-orb {
                width: 20px;
                height: 20px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils me-2"></i>Aurelian Restaurant
            </a>
            
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu.php">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="cart-count">0</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i> <?php echo $_SESSION['full_name'] ?? 'Customer'; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Welcome Section - IMPROVED & REDESIGNED -->
        <div class="welcome-section">
            <div class="container">
                <div class="row align-items-center">
                    <!-- Left Column: Text Content -->
                    <div class="col-md-8">
                        <div class="welcome-content-wrapper">
                            <!-- Profile Avatar Circle - CENTERED -->
                            <div class="profile-avatar-circle">
                                <!-- GANTI DENGAN FOTO ANDA -->
                                <!-- <img src="../assets/img/your-photo.jpg" alt="Profile Photo" class="profile-photo">-->
                                
                                <!-- Fallback Initials (tampil jika foto tidak ada) -->
                                <div class="avatar-initials">JD</div>
                                
                                <!-- Animated Rings -->
                                <div class="avatar-ring ring-1"></div>
                                <div class="avatar-ring ring-2"></div>
                                <div class="avatar-ring ring-3"></div>
                                
                                <!-- Status Indicator -->
                                <div class="avatar-status"></div>
                            </div>
                            
                            <!-- Welcome Text - CENTERED -->
                            <div class="welcome-text-container">
                                <h1 class="welcome-title">Welcome, <?php echo $_SESSION['full_name'] ?? 'Customer'; ?>!</h1>
                                <p class="welcome-role">
                                    <i class="fas fa-crown me-2"></i>
                                    Premium Member
                                </p>
                            </div>
                            
                            <!-- Description -->
                            <p class="welcome-description">
                                Enjoy our premium dining experience. Order your favorite food online or reserve a table at our restaurant.
                            </p>
                            
                            <!-- Action Buttons - CENTERED WITH BETTER SPACING -->
                            <div class="welcome-actions">
                                <a href="menu.php" class="btn btn-primary btn-lg">
                                    <i class="fas fa-utensils me-2"></i> Order Now
                                </a>
                                <a href="reservation.php" class="btn btn-outline-light btn-lg">
                                    <i class="fas fa-calendar-alt me-2"></i> Book a Table
                                </a>
                            </div>
                            
                            <!-- Additional Info - CENTERED -->
                            <div class="welcome-meta">
                                <div class="meta-item">
                                    <i class="fas fa-star text-warning"></i>
                                    <span>Loyalty Points: <strong>1,250</strong></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-gift text-success"></i>
                                    <span>Special Offers: <strong>Available</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- =================================================== -->
                    <!-- PERUBAHAN DI SINI: Right Column - DIGANTI DENGAN GAMBAR MAKANAN -->
                    <!-- =================================================== -->
                    <div class="col-md-4">
                        <div class="welcome-illustration">
                            <div class="food-image-container">
                                <!-- Gambar makanan premium Anda - GANTI PATH SESUAI KEBUTUHAN -->
                                <img src="../assets/img/img37.svg" alt="Premium Dining Experience" class="food-image">
                                
                                <!-- Floating decorative orbs -->
                                <div class="food-orb"></div>
                                <div class="food-orb"></div>
                                <div class="food-orb"></div>
                                <div class="food-orb"></div>
                            </div>
                        </div>
                    </div>
                    <!-- =================================================== -->
                    <!-- AKHIR PERUBAHAN -->
                    <!-- =================================================== -->
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="row mt-5">
            <div class="col-md-4">
                <div class="card text-center p-4">
                    <div class="card-body">
                        <i class="fas fa-shopping-cart fa-3x text-primary mb-3"></i>
                        <h3>0</h3>
                        <p class="text-muted">Total Orders</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-4">
                    <div class="card-body">
                        <i class="fas fa-star fa-3x text-warning mb-3"></i>
                        <h3>0</h3>
                        <p class="text-muted">Average Rating</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center p-4">
                    <div class="card-body">
                        <i class="fas fa-tag fa-3x text-success mb-3"></i>
                        <h3>Rp 0</h3>
                        <p class="text-muted">Total Spent</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders -->
        <div class="card mt-5">
            <div class="card-header">
                <h4 class="mb-0">Recent Orders</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Items</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">No orders yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Aurelian Restaurant</h5>
                    <p>Fine dining experience with premium cuisine.</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>&copy; 2024 Aurelian Restaurant. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        console.log("Customer dashboard loaded successfully");
        
        // Initialize animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effect to cards
            const cards = document.querySelectorAll('.card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px)';
                    this.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 2px 10px rgba(0,0,0,0.1)';
                });
            });
            
            // Add click animation to buttons
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    // Create ripple effect
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        background: rgba(255, 255, 255, 0.6);
                        transform: scale(0);
                        animation: ripple-animation 0.6s linear;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        pointer-events: none;
                    `;
                    
                    this.appendChild(ripple);
                    setTimeout(() => ripple.remove(), 600);
                });
            });
            
            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple-animation {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
            
            // Make avatar clickable for photo upload (placeholder functionality)
            const avatar = document.querySelector('.profile-avatar-circle');
            avatar.addEventListener('click', function() {
                alert('Click this area to upload your profile photo!');
            });
        });
    </script>
</body>
</html>