<?php
// admin/sidebar.php
if (!isset($_SESSION)) {
    session_start();
}

// Mendapatkan halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- SIDEBAR STYLING -->
<style>
    /* VARIABLES */
    :root {
        --primary-color: #0b3b2e;
        --secondary-color: #0f5132;
        --accent-color: #d4af37;
        --sidebar-width: 280px;
    }
    
    /* SIDEBAR CONTAINER */
    .sidebar-container {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: var(--sidebar-width);
        background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        color: white;
        z-index: 1000;
        overflow-y: auto;
        overflow-x: visible; /* Pastikan tidak ada scroll horizontal */
        box-shadow: 2px 0 20px rgba(0,0,0,0.15);
        display: flex;
        flex-direction: column;
    }
    
    /* SIDEBAR HEADER */
    .sidebar-header {
        padding: 25px 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        background: rgba(0, 0, 0, 0.1);
        flex-shrink: 0;
    }
    
    .sidebar-header h3 {
        margin: 15px 0 5px;
        font-size: 1.4rem;
        font-weight: 700;
        letter-spacing: 1px;
        white-space: nowrap;
        overflow: visible;
        text-overflow: clip;
    }
    
    .sidebar-header p {
        margin: 5px 0;
        color: rgba(255,255,255,0.9);
        font-size: 0.9rem;
        white-space: nowrap;
        overflow: visible;
        text-overflow: clip;
    }
    
    .sidebar-header small {
        display: inline-block;
        background: var(--accent-color);
        color: var(--primary-color);
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 8px;
        white-space: nowrap;
    }
    
    /* SIDEBAR MENU CONTAINER */
    .sidebar-menu-container {
        flex: 1;
        padding: 20px 15px;
        overflow-y: auto;
        overflow-x: visible;
    }
    
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
        width: 100%;
    }
    
    .sidebar-menu li {
        margin: 8px 0;
        width: 100%;
    }
    
    /* MENU ITEM - LENGKAP TIDAK TERPOTONG */
    .sidebar-menu li a {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        color: rgba(255, 255, 255, 0.85);
        text-decoration: none;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 12px;
        border: 1px solid transparent;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: visible;
        width: 100%;
        min-width: 100%;
        box-sizing: border-box;
    }
    
    /* Border kiri untuk menu aktif */
    .sidebar-menu li a::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: var(--accent-color);
        transform: scaleY(0);
        transition: transform 0.3s ease;
        transform-origin: bottom;
    }
    
    .sidebar-menu li.active a::before {
        transform: scaleY(1);
    }
    
    /* Hover Effect */
    .sidebar-menu li a:hover {
        background: rgba(255, 255, 255, 0.08);
        transform: translateX(8px);
        border-color: rgba(212, 175, 55, 0.3);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }
    
    /* Active Menu Styling */
    .sidebar-menu li.active a {
        background: linear-gradient(135deg, rgba(212, 175, 55, 0.15), rgba(11, 59, 46, 0.3));
        border-color: rgba(212, 175, 55, 0.5);
        box-shadow: 0 10px 30px rgba(212, 175, 55, 0.2);
        color: white;
    }
    
    /* ICON CONTAINER - TERLIHAT LENGKAP */
    .menu-icon-wrapper {
        width: 42px;
        height: 42px;
        min-width: 42px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        transition: all 0.3s ease;
        flex-shrink: 0;
    }
    
    .sidebar-menu li a:hover .menu-icon-wrapper,
    .sidebar-menu li.active a .menu-icon-wrapper {
        background: var(--accent-color);
        transform: rotate(5deg) scale(1.1);
    }
    
    .menu-icon-wrapper i {
        font-size: 18px;
        color: rgba(255, 255, 255, 0.8);
        transition: all 0.3s ease;
        display: block;
    }
    
    .sidebar-menu li a:hover .menu-icon-wrapper i,
    .sidebar-menu li.active a .menu-icon-wrapper i {
        color: var(--primary-color);
    }
    
    /* MENU TEXT - TERLIHAT LENGKAP */
    .menu-text {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;
        overflow: visible;
    }
    
    .menu-title {
        font-weight: 600;
        font-size: 14px;
        margin-bottom: 3px;
        white-space: nowrap;
        overflow: visible;
        text-overflow: clip;
        display: block;
    }
    
    .menu-subtitle {
        font-size: 11px;
        color: rgba(255, 255, 255, 0.6);
        font-weight: 400;
        white-space: nowrap;
        overflow: visible;
        text-overflow: clip;
        display: block;
    }
    
    /* MENU BADGE/ARROW */
    .menu-badge {
        opacity: 0;
        transform: translateX(-10px);
        transition: all 0.3s ease;
        min-width: 20px;
        flex-shrink: 0;
    }
    
    .sidebar-menu li a:hover .menu-badge,
    .sidebar-menu li.active a .menu-badge {
        opacity: 1;
        transform: translateX(0);
    }
    
    .menu-badge i {
        color: var(--accent-color);
        font-size: 12px;
        display: block;
    }
    
    /* SIDEBAR FOOTER */
    .sidebar-footer {
        padding: 15px 20px;
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        background: rgba(0, 0, 0, 0.1);
        flex-shrink: 0;
    }
    
    .system-status {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
    }
    
    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: 10px;
    }
    
    .status-indicator.online {
        background: #4CAF50;
        box-shadow: 0 0 10px rgba(76, 175, 80, 0.5);
        animation: pulse 2s infinite;
    }
    
    .server-time {
        display: flex;
        align-items: center;
        color: rgba(255, 255, 255, 0.6);
        font-size: 12px;
    }
    
    .server-time i {
        margin-right: 8px;
        font-size: 12px;
    }
    
    /* ANIMATIONS */
    @keyframes pulse {
        0% { opacity: 1; }
        50% { opacity: 0.5; }
        100% { opacity: 1; }
    }
    
    @keyframes menuGlow {
        0% { box-shadow: 0 5px 15px rgba(212, 175, 55, 0.2); }
        50% { box-shadow: 0 5px 20px rgba(212, 175, 55, 0.4); }
        100% { box-shadow: 0 5px 15px rgba(212, 175, 55, 0.2); }
    }
    
    .sidebar-menu li.active a {
        animation: menuGlow 2s infinite;
    }
    
    /* SCROLLBAR STYLING */
    .sidebar-container::-webkit-scrollbar {
        width: 6px;
    }
    
    .sidebar-container::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 3px;
    }
    
    .sidebar-container::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.3);
        border-radius: 3px;
    }
    
    .sidebar-container::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.5);
    }
    
    /* RESPONSIVE DESIGN */
    @media (max-width: 1024px) {
        .sidebar-container {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            width: 280px;
        }
        
        .sidebar-container.active {
            transform: translateX(0);
        }
    }
    
    /* SIDEBAR TOGGLE BUTTON UNTUK MOBILE */
    .sidebar-toggle {
        display: none;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1100;
        background: var(--primary-color);
        color: white;
        border: none;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        cursor: pointer;
        font-size: 18px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        align-items: center;
        justify-content: center;
    }
    
    @media (max-width: 1024px) {
        .sidebar-toggle {
            display: flex;
        }
    }
    
    /* OVERLAY UNTUK MOBILE */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
    }
    
    @media (max-width: 1024px) {
        .sidebar-overlay.active {
            display: block;
        }
    }
</style>

<!-- SIDEBAR HTML STRUCTURE -->
<div class="sidebar-container" id="sidebarContainer">
    <div class="sidebar-header">
        <h3>Aurelian Admin</h3>
        <p><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'Administrator'); ?></p>
        <small><?php echo ucfirst($_SESSION['role'] ?? 'admin'); ?></small>
    </div>
    
    <div class="sidebar-menu-container">
        <ul class="sidebar-menu">
            <li class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">
                <a href="index.php">
                    <div class="menu-icon-wrapper">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="menu-text">
                        <span class="menu-title">Dashboard</span>
                        <span class="menu-subtitle">Overview & Statistics</span>
                    </div>
                    <div class="menu-badge">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            <li class="<?php echo $current_page == 'products.php' ? 'active' : ''; ?>">
                <a href="products.php">
                    <div class="menu-icon-wrapper">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <div class="menu-text">
                        <span class="menu-title">Products</span>
                        <span class="menu-subtitle">Manage Menu Items</span>
                    </div>
                    <div class="menu-badge">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            <li class="<?php echo $current_page == 'categories.php' ? 'active' : ''; ?>">
                <a href="categories.php">
                    <div class="menu-icon-wrapper">
                        <i class="fas fa-list"></i>
                    </div>
                    <div class="menu-text">
                        <span class="menu-title">Categories</span>
                        <span class="menu-subtitle">Food Categories</span>
                    </div>
                    <div class="menu-badge">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            <li class="<?php echo $current_page == 'orders.php' ? 'active' : ''; ?>">
                <a href="orders.php">
                    <div class="menu-icon-wrapper">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="menu-text">
                        <span class="menu-title">Orders</span>
                        <span class="menu-subtitle">Customer Orders</span>
                    </div>
                    <div class="menu-badge">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            <li class="<?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                <a href="users.php">
                    <div class="menu-icon-wrapper">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="menu-text">
                        <span class="menu-title">Users</span>
                        <span class="menu-subtitle">Manage Users</span>
                    </div>
                    <div class="menu-badge">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            <li class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
                <a href="reports.php">
                    <div class="menu-icon-wrapper">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <div class="menu-text">
                        <span class="menu-title">Reports</span>
                        <span class="menu-subtitle">Sales Analytics</span>
                    </div>
                    <div class="menu-badge">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            <li class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                <a href="profile.php">
                    <div class="menu-icon-wrapper">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="menu-text">
                        <span class="menu-title">Profile</span>
                        <span class="menu-subtitle">Account Settings</span>
                    </div>
                    <div class="menu-badge">
                        <i class="fas fa-chevron-right"></i>
                    </div>
                </a>
            </li>
            <li>
                <a href="../logout.php">
                    <div class="menu-icon-wrapper">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                    <div class="menu-text">
                        <span class="menu-title">Logout</span>
                        <span class="menu-subtitle">Exit System</span>
                    </div>
                    <div class="menu-badge">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
        <div class="system-status">
            <div class="status-indicator online"></div>
            <span>System Online</span>
        </div>
        <div class="server-time">
            <i class="far fa-clock"></i>
            <span id="server-time"><?php echo date('H:i'); ?></span>
        </div>
    </div>
</div>

<!-- Toggle Button untuk Mobile -->
<button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<!-- Overlay untuk Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- SCRIPT UNTUK SIDEBAR -->
<script>
    // Update waktu server
    function updateServerTime() {
        const now = new Date();
        const timeString = now.getHours().toString().padStart(2, '0') + ':' + 
                          now.getMinutes().toString().padStart(2, '0');
        const serverTimeElement = document.getElementById('server-time');
        if (serverTimeElement) {
            serverTimeElement.textContent = timeString;
        }
    }
    
    // Update waktu setiap menit
    setInterval(updateServerTime, 60000);
    updateServerTime(); // Jalankan pertama kali
    
    // Toggle sidebar untuk mobile
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarContainer = document.getElementById('sidebarContainer');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (sidebarToggle && sidebarContainer) {
        // Toggle sidebar
        sidebarToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            sidebarContainer.classList.toggle('active');
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('active');
            }
        });
        
        // Tutup sidebar saat klik overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebarContainer.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            });
        }
        
        // Auto-close sidebar saat klik menu item di mobile
        const menuLinks = document.querySelectorAll('.sidebar-menu a');
        menuLinks.forEach(link => {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 1024) {
                    sidebarContainer.classList.remove('active');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('active');
                    }
                }
            });
        });
        
        // Tutup sidebar saat klik di luar sidebar di mobile
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 1024) {
                if (!sidebarContainer.contains(e.target) && 
                    !sidebarToggle.contains(e.target) && 
                    sidebarContainer.classList.contains('active')) {
                    sidebarContainer.classList.remove('active');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('active');
                    }
                }
            }
        });
    }
    
    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024 && sidebarContainer) {
            sidebarContainer.classList.remove('active');
            if (sidebarOverlay) {
                sidebarOverlay.classList.remove('active');
            }
        }
    });
    
    // Pastikan semua elemen sidebar terlihat
    document.addEventListener('DOMContentLoaded', function() {
        // Periksa apakah ada elemen yang terpotong
        const menuItems = document.querySelectorAll('.sidebar-menu li a');
        menuItems.forEach(item => {
            const icon = item.querySelector('.menu-icon-wrapper i');
            const title = item.querySelector('.menu-title');
            const subtitle = item.querySelector('.menu-subtitle');
            
            // Pastikan semua elemen terlihat
            if (icon) icon.style.visibility = 'visible';
            if (title) {
                title.style.visibility = 'visible';
                title.style.opacity = '1';
            }
            if (subtitle) {
                subtitle.style.visibility = 'visible';
                subtitle.style.opacity = '1';
            }
        });
        
        // Periksa overflow sidebar
        const sidebarMenuContainer = document.querySelector('.sidebar-menu-container');
        if (sidebarMenuContainer && sidebarMenuContainer.scrollHeight > sidebarMenuContainer.clientHeight) {
            sidebarMenuContainer.style.overflowY = 'auto';
        } else {
            sidebarMenuContainer.style.overflowY = 'visible';
        }
    });
</script>