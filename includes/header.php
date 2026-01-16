<?php
// File header umum untuk customer pages
if (!isset($page_title)) {
    $page_title = 'Aurelian Restaurant';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Aurelian Restaurant</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Animate.css -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
    
    <style>
        /* Modern Profile Dropdown Styles */
        .user-nav {
            position: relative;
            display: inline-block;
        }
        
        .user-toggle {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            text-decoration: none !important;
        }
        
        .user-toggle:hover {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(212, 175, 55, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0b3b2e 0%, #d4af37 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            overflow: hidden;
            border: 2px solid white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .user-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }
        
        .user-name {
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            line-height: 1.2;
        }
        
        .user-role {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .user-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            width: 280px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 1000;
            margin-top: 10px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.08);
        }
        
        .user-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-header {
            background: linear-gradient(135deg, #0b3b2e 0%, #0f5132 100%);
            padding: 20px;
            color: white;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .dropdown-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #d4af37 0%, #ffd700 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
            font-weight: 700;
            font-size: 20px;
            border: 3px solid white;
        }
        
        .dropdown-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .dropdown-user-info h4 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }
        
        .dropdown-user-info p {
            margin: 5px 0 0;
            font-size: 0.85rem;
            opacity: 0.9;
        }
        
        .dropdown-menu {
            list-style: none;
            padding: 10px 0;
            margin: 0;
        }
        
        .dropdown-menu li {
            margin: 0;
        }
        
        .dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: #333;
            text-decoration: none;
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 0.95rem;
        }
        
        .dropdown-menu a:hover {
            background: #f8f9fa;
            color: #0b3b2e;
            padding-left: 25px;
        }
        
        .dropdown-menu a i {
            width: 20px;
            color: #0b3b2e;
            font-size: 1.1rem;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #e9ecef;
            margin: 8px 20px;
        }
        
        .dropdown-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }
        
        .logout-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 10px 20px;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
            color: white;
        }
        
        /* Badge styles */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #ff4757;
            color: white;
            font-size: 0.7rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Cart badge */
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff6b6b;
            color: white;
            font-size: 0.7rem;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        
        /* Floating animation for cart */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0px); }
        }
        
        .floating-cart {
            animation: float 3s ease-in-out infinite;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .user-name, .user-role {
                display: none;
            }
            
            .user-toggle {
                padding: 8px;
            }
            
            .user-dropdown {
                position: fixed;
                top: 60px;
                right: 10px;
                left: 10px;
                width: auto;
                z-index: 1050;
            }
        }
        
        /* Dark mode support */
        @media (prefers-color-scheme: dark) {
            .user-dropdown {
                background: #2c3e50;
                border-color: #34495e;
            }
            
            .dropdown-header {
                background: linear-gradient(135deg, #1a252f 0%, #2c3e50 100%);
            }
            
            .dropdown-menu a {
                color: #ecf0f1;
            }
            
            .dropdown-menu a:hover {
                background: #34495e;
                color: #fff;
            }
            
            .dropdown-divider {
                background: #34495e;
            }
            
            .dropdown-footer {
                background: #1a252f;
                border-color: #34495e;
            }
        }
    </style>
    
    <?php if (isset($custom_css)): ?>
    <style><?php echo $custom_css; ?></style>
    <?php endif; ?>
</head>
<body>
    <!-- Main container -->
    <div class="main-container">