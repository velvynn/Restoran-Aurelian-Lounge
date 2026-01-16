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

$user_id = $_SESSION['user_id'];

// Get user data
$db->query('SELECT * FROM users WHERE id = :id');
$db->bind(':id', $user_id);
$user = $db->single();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone'] ?? '');
        
        // Check if email exists for other users
        $db->query('SELECT id FROM users WHERE email = :email AND id != :id');
        $db->bind(':email', $email);
        $db->bind(':id', $user_id);
        $existing = $db->single();
        
        if ($existing) {
            $_SESSION['error'] = 'Email already exists!';
        } else {
            $db->query('UPDATE users SET full_name = :full_name, email = :email, phone = :phone WHERE id = :id');
            $db->bind(':full_name', $full_name);
            $db->bind(':email', $email);
            $db->bind(':phone', $phone);
            $db->bind(':id', $user_id);
            
            if ($db->execute()) {
                $_SESSION['success'] = 'Profile updated successfully!';
                $_SESSION['full_name'] = $full_name;
                $_SESSION['email'] = $email;
                header('Location: profile.php');
                exit();
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if ($new_password !== $confirm_password) {
            $_SESSION['error'] = 'New passwords do not match!';
        } elseif (strlen($new_password) < 6) {
            $_SESSION['error'] = 'Password must be at least 6 characters!';
        } elseif (!password_verify($current_password, $user['password'])) {
            $_SESSION['error'] = 'Current password is incorrect!';
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $db->query('UPDATE users SET password = :password WHERE id = :id');
            $db->bind(':password', $hashed_password);
            $db->bind(':id', $user_id);
            
            if ($db->execute()) {
                $_SESSION['success'] = 'Password changed successfully!';
                header('Location: profile.php');
                exit();
            }
        }
    }
}

$page_title = 'Admin Profile';
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
            width: 100%;
            max-width: calc(100vw - 280px);
            overflow-x: hidden;
        }
        
        .content-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            width: 100%;
        }
        
        .profile-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            width: 100%;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0 auto 20px;
            border: 5px solid white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .nav-tabs {
            border-bottom: 2px solid #dee2e6;
            margin-bottom: 20px;
            flex-wrap: nowrap;
            overflow-x: auto;
            overflow-y: hidden;
            width: 100%;
        }
        
        .nav-tabs .nav-link {
            color: var(--primary-color);
            font-weight: 500;
            white-space: nowrap;
            padding: 10px 20px;
            border: 1px solid transparent;
            border-radius: 5px 5px 0 0;
            margin-bottom: -2px;
        }
        
        .nav-tabs .nav-link.active {
            background: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        
        .nav-tabs .nav-link:hover:not(.active) {
            border-color: #dee2e6;
            background-color: #f8f9fa;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
        }
        
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
            width: 100%;
        }
        
        .stat-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .stat-box i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .stat-box h4 {
            margin: 0;
            color: var(--primary-color);
            font-size: 1.5rem;
        }
        
        .tab-content {
            width: 100%;
        }
        
        .tab-pane {
            width: 100%;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
        }
        
        @media (max-width: 1024px) {
            .main-content {
                margin-left: 0;
                width: 100%;
                max-width: 100%;
                padding: 15px;
            }
            
            .profile-card {
                padding: 20px;
            }
            
            .profile-stats {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .profile-stats {
                grid-template-columns: 1fr;
            }
            
            .nav-tabs {
                flex-wrap: wrap;
            }
            
            .nav-tabs .nav-link {
                font-size: 0.9rem;
                padding: 8px 15px;
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
                    <h1>Admin Profile</h1>
                    <p class="text-muted mb-0">Manage your account information</p>
                </div>
                
                <?php displayMessage(); ?>
                
                <div class="profile-card">
                    <!-- Profile Header -->
                    <div class="text-center mb-4">
                        <div class="profile-avatar">
                            <?php echo substr($user['full_name'], 0, 2); ?>
                        </div>
                        <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p class="text-muted">
                            <i class="fas fa-user-tag me-2"></i>
                            <?php echo ucfirst($user['role']); ?> | 
                            <i class="fas fa-envelope me-2"></i>
                            <?php echo htmlspecialchars($user['email']); ?>
                        </p>
                    </div>
                    
                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="profileTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button">
                                <i class="fas fa-user-circle me-2"></i>Profile Information
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="password-tab" data-bs-toggle="tab" data-bs-target="#password" type="button">
                                <i class="fas fa-lock me-2"></i>Change Password
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button">
                                <i class="fas fa-history me-2"></i>Recent Activity
                            </button>
                        </li>
                    </ul>
                    
                    <!-- Tab Content -->
                    <div class="tab-content" id="profileTabContent">
                        <!-- Profile Information -->
                        <div class="tab-pane fade show active" id="profile">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" class="form-control" name="full_name" 
                                               value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" name="email" 
                                               value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Phone Number</label>
                                        <input type="tel" class="form-control" name="phone" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Role</label>
                                        <input type="text" class="form-control" value="<?php echo ucfirst($user['role']); ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Member Since</label>
                                        <input type="text" class="form-control" 
                                               value="<?php echo date('F d, Y', strtotime($user['created_at'])); ?>" readonly>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <button type="submit" name="update_profile" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>Update Profile
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Change Password -->
                        <div class="tab-pane fade" id="password">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Current Password</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" name="new_password" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Confirm New Password</label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                    <div class="col-md-6 mb-3 d-flex align-items-end">
                                        <div>
                                            <small class="text-muted">Password must be at least 6 characters long</small>
                                        </div>
                                    </div>
                                    <div class="col-12 mt-3">
                                        <button type="submit" name="change_password" class="btn btn-primary">
                                            <i class="fas fa-key me-2"></i>Change Password
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        
                        <!-- Recent Activity -->
                        <div class="tab-pane fade" id="activity">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Activity</th>
                                            <th>Date</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><i class="fas fa-sign-in-alt text-success me-2"></i>Login to Admin Panel</td>
                                            <td><?php echo date('Y-m-d H:i:s'); ?></td>
                                            <td><?php echo $_SERVER['REMOTE_ADDR']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-eye text-info me-2"></i>Viewed Dashboard</td>
                                            <td><?php echo date('Y-m-d H:i:s', strtotime('-1 hour')); ?></td>
                                            <td><?php echo $_SERVER['REMOTE_ADDR']; ?></td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-user-clock text-warning me-2"></i>Last Login</td>
                                            <td><?php echo $user['last_login'] ? date('Y-m-d H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                                            <td>N/A</td>
                                        </tr>
                                        <tr>
                                            <td><i class="fas fa-calendar-day text-primary me-2"></i>Account Created</td>
                                            <td><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                                            <td>N/A</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Account Statistics -->
                <div class="profile-stats">
                    <div class="stat-box">
                        <i class="fas fa-chart-line text-primary"></i>
                        <h4>48</h4>
                        <p class="text-muted">Total Actions Today</p>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-clock text-warning"></i>
                        <h4>2h 30m</h4>
                        <p class="text-muted">Active Session Time</p>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-shield-alt text-success"></i>
                        <h4>Admin</h4>
                        <p class="text-muted">Account Privileges</p>
                    </div>
                    <div class="stat-box">
                        <i class="fas fa-database text-info"></i>
                        <h4>Active</h4>
                        <p class="text-muted">Account Status</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize tabs
        const triggerTabList = document.querySelectorAll('#profileTab button');
        triggerTabList.forEach(triggerEl => {
            const tabTrigger = new bootstrap.Tab(triggerEl);
            triggerEl.addEventListener('click', event => {
                event.preventDefault();
                tabTrigger.show();
            });
        });
        
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            const passwordForm = document.querySelector('form[action*="change_password"]');
            if (passwordForm) {
                passwordForm.addEventListener('submit', function(e) {
                    const newPassword = this.querySelector('input[name="new_password"]').value;
                    const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
                    
                    if (newPassword.length < 6) {
                        e.preventDefault();
                        alert('Password must be at least 6 characters long!');
                        return false;
                    }
                    
                    if (newPassword !== confirmPassword) {
                        e.preventDefault();
                        alert('New passwords do not match!');
                        return false;
                    }
                    
                    return true;
                });
            }
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                var alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);
        });
    </script>
</body>
</html>