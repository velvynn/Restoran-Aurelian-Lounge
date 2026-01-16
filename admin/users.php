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

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_user'])) {
        $id = intval($_POST['id']);
        $full_name = sanitize($_POST['full_name']);
        $email = sanitize($_POST['email']);
        $phone = sanitize($_POST['phone'] ?? '');
        $role = sanitize($_POST['role']);
        
        $db->query('UPDATE users SET full_name = :full_name, email = :email, phone = :phone, role = :role WHERE id = :id');
        $db->bind(':full_name', $full_name);
        $db->bind(':email', $email);
        $db->bind(':phone', $phone);
        $db->bind(':role', $role);
        $db->bind(':id', $id);
        
        if ($db->execute()) {
            $_SESSION['success'] = 'User updated successfully!';
        }
    }
    
    if (isset($_POST['delete_user'])) {
        $id = intval($_POST['id']);
        
        // Cannot delete yourself
        if ($id == $_SESSION['user_id']) {
            $_SESSION['error'] = 'Cannot delete your own account!';
        } else {
            $db->query('DELETE FROM users WHERE id = :id');
            $db->bind(':id', $id);
            
            if ($db->execute()) {
                $_SESSION['success'] = 'User deleted successfully!';
            }
        }
    }
    
    header('Location: users.php');
    exit();
}

// Get all users
$db->query('SELECT * FROM users ORDER BY created_at DESC');
$users = $db->resultSet();

// Get user statistics
$db->query('SELECT 
    COUNT(*) as total_users,
    COUNT(CASE WHEN role = "admin" THEN 1 END) as admin_count,
    COUNT(CASE WHEN role = "manager" THEN 1 END) as manager_count,
    COUNT(CASE WHEN role = "user" THEN 1 END) as customer_count,
    COUNT(CASE WHEN role IN ("chef", "waiter", "cashier") THEN 1 END) as staff_count
    FROM users');
$user_stats = $db->single();

$page_title = 'Manage Users';
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
        
        .role-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .role-admin { background: #e3f2fd; color: #1565c0; }
        .role-manager { background: #e8f5e9; color: #2e7d32; }
        .role-user { background: #fff3e0; color: #ef6c00; }
        .role-staff { background: #f3e5f5; color: #7b1fa2; }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }
        
        .users-table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            min-width: 800px;
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
            .users-table-container {
                min-width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .stats-cards {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .content-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
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
                    <h1>Manage Users</h1>
                    <p class="text-muted mb-0">View and manage system users</p>
                </div>
                
                <?php displayMessage(); ?>
                
                <!-- User Statistics -->
                <div class="stats-cards">
                    <div class="stat-card">
                        <h3><?php echo $user_stats['total_users']; ?></h3>
                        <p>Total Users</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $user_stats['admin_count']; ?></h3>
                        <p>Administrators</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $user_stats['manager_count']; ?></h3>
                        <p>Managers</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $user_stats['customer_count']; ?></h3>
                        <p>Customers</p>
                    </div>
                    <div class="stat-card">
                        <h3><?php echo $user_stats['staff_count']; ?></h3>
                        <p>Staff Members</p>
                    </div>
                </div>
                
                <!-- Users Table -->
                <div class="users-table-container">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Joined</th>
                                    <th>Last Login</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="user-avatar me-3">
                                                <?php echo substr($user['full_name'], 0, 2); ?>
                                            </div>
                                            <div>
                                                <div><?php echo htmlspecialchars($user['full_name']); ?></div>
                                                <small class="text-muted">@<?php echo htmlspecialchars($user['username']); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['phone'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="role-badge role-<?php echo $user['role']; ?>">
                                            <?php echo ucfirst($user['role']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                                    <td><?php echo $user['last_login'] ? date('d/m/Y H:i', strtotime($user['last_login'])) : 'Never'; ?></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                Actions
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li>
                                                    <button type="button" class="dropdown-item" 
                                                            onclick="editUser(<?php echo $user['id']; ?>, '<?php echo htmlspecialchars($user['full_name']); ?>', '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['phone'] ?? ''); ?>', '<?php echo $user['role']; ?>')">
                                                        <i class="fas fa-edit me-2"></i> Edit
                                                    </button>
                                                </li>
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                <li>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                        <button type="submit" name="delete_user" class="dropdown-item text-danger" 
                                                                onclick="return confirm('Delete this user? This action cannot be undone.')">
                                                            <i class="fas fa-trash me-2"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                                <?php endif; ?>
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
    
    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--primary-color); color: white;">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="id" id="editUserId">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" name="full_name" id="editUserName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editUserEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" id="editUserPhone">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" id="editUserRole" required>
                                <option value="user">Customer</option>
                                <option value="admin">Administrator</option>
                                <option value="manager">Manager</option>
                                <option value="chef">Chef</option>
                                <option value="waiter">Waiter</option>
                                <option value="cashier">Cashier</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="update_user" class="btn btn-primary">Update User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function editUser(id, name, email, phone, role) {
            document.getElementById('editUserId').value = id;
            document.getElementById('editUserName').value = name;
            document.getElementById('editUserEmail').value = email;
            document.getElementById('editUserPhone').value = phone;
            document.getElementById('editUserRole').value = role;
            
            var editModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            editModal.show();
        }
    </script>
</body>
</html>