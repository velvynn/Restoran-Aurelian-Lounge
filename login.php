<?php 
// Tambahkan di atas untuk cek session
session_start();

// Debug: cek apakah ada session error/success
if (isset($_SESSION['error'])) {
    // echo "<div style='background: red; color: white; padding: 10px;'>Error: " . $_SESSION['error'] . "</div>";
}
if (isset($_SESSION['success'])) {
    // echo "<div style='background: green; color: white; padding: 10px;'>Success: " . $_SESSION['success'] . "</div>";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RESTAURANT AURELIAN - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login-style.css">
    <style>
        /* Tambahan untuk debug */
        .debug-info {
            position: fixed;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.7);
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            z-index: 10000;
        }
        
        .alert-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
            max-width: 400px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
        }
        
        .alert-error {
            background-color: #f44336;
            border-left: 5px solid #d32f2f;
        }
        
        .alert-success {
            background-color: #4CAF50;
            border-left: 5px solid #388E3C;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Debug Info (tampilkan jika perlu) -->
    <?php if (isset($_SESSION['error']) || isset($_SESSION['success'])): ?>
    <div class="debug-info">
        <?php 
        if (isset($_SESSION['error'])) echo "Error: " . $_SESSION['error'];
        if (isset($_SESSION['success'])) echo "Success: " . $_SESSION['success'];
        ?>
    </div>
    <?php endif; ?>
    
    <div class="container">
        <div class="header">
            <h1>RESTAURANT AURELIAN</h1>
            <div class="subtitle">Fine Dining Experience</div>
        </div>
        
        <div class="login-container">
            <div class="welcome-section">
                <h2>Welcome Back</h2>
                <p>Sign in to receive dashboard</p>
            </div>
            
            <div class="working-hours">
                <p><i class="far fa-clock"></i> 1000 AM - 1800 PM</p>
                <p><i class="fas fa-phone"></i> (00) 8886-8888</p>
            </div>
            
            <form id="loginForm" action="includes/login_process.php" method="POST" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="username">Enter your username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Enter your username" required 
                           value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Enter your password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="remember" name="remember" checked>
                    <label for="remember">Remember me</label>
                </div>
                
                <div class="system-status">
                    <p>System Status: <span class="online-dot">●</span> Online</p>
                    <div class="users-count">48 Active Users - Other user</div>
                </div>
                
                <div class="form-group">
                    <label for="role">Select Your Role</label>
                    <select id="role" name="role" class="role-select" required>
                        <option value="">Select your role</option>
                        <option value="admin">Administrator</option>
                        <option value="manager">Manager</option>
                        <option value="chef">Chef / Koki</option>
                        <option value="waiter">Waiter / Pelayan</option>
                        <option value="cashier">Cashier / Kasir</option>
                        <option value="customer">Customer / Pelanggan</option>
                    </select>
                </div>
                
                <div class="role-info-box">
                    <div class="role-table-container">
                        <table class="role-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Manager</th>
                                    <th>Cashier</th>
                                    <th>Waiter</th>
                                    <th>Chef</th>
                                    <th>Customer</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>La Ode Kevin</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                </tr>
                                <tr>
                                    <td>Rani Maharani</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                </tr>
                                <tr>
                                    <td>Muggy Soewarman</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                    <td>✓</td>
                                </tr>
                                
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <button type="submit" class="signin-button">SIGN IN NOW</button>
                
                <div class="register-link">
                    <p>Don't have an account yet?</p>
                    <a href="register.php" class="register-button">JOIN ELITE TEAM →</a>
                </div>
            </form>
        </div>
        
        <div class="footer">
            <div class="support-email">Support@differentsources.com</div>
            <div class="copyright">© 2024 Networver Ettie. All rights reserved.</div>
        </div>
    </div>

    <script src="assets/js/login-script.js"></script>
    <script>
        function validateForm() {
            var username = document.getElementById('username').value;
            var password = document.getElementById('password').value;
            var role = document.getElementById('role').value;
            
            if (!username || !password || !role) {
                showAlert('Harap isi semua field!', 'error');
                return false;
            }
            
            // For testing - auto fill common credentials
            if (username === 'admin' && password === 'admin123') {
                document.getElementById('role').value = 'admin';
            }
            
            return true;
        }
        
        function showAlert(message, type) {
            // Remove existing alerts
            var existingAlert = document.querySelector('.alert-message');
            if (existingAlert) {
                existingAlert.remove();
            }
            
            // Create alert element
            var alertElement = document.createElement('div');
            alertElement.className = 'alert-message alert-' + type;
            
            var icon = type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' : '<i class="fas fa-check-circle"></i>';
            alertElement.innerHTML = icon + '<span>' + message + '</span>';
            
            document.body.appendChild(alertElement);
            
            // Remove after 5 seconds
            setTimeout(function() {
                if (alertElement.parentNode) {
                    alertElement.style.animation = 'slideOut 0.3s ease-out';
                    setTimeout(function() {
                        if (alertElement.parentNode) {
                            alertElement.remove();
                        }
                    }, 300);
                }
            }, 5000);
        }
        
        // Auto-fill for testing
        document.addEventListener('DOMContentLoaded', function() {
            // For quick testing
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('test') === '1') {
                document.getElementById('username').value = 'admin';
                document.getElementById('password').value = 'aurelian123';
                document.getElementById('role').value = 'admin';
            }
        });
    </script>
</body>
</html>
<?php 
// Clear messages after displaying
unset($_SESSION['error']);
unset($_SESSION['success']);
?>