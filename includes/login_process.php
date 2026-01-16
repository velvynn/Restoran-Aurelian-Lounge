<?php
// Start session pertama kali di file ini
session_start();

// Debug: Tampilkan data POST
// echo "<pre>";
// print_r($_POST);
// echo "</pre>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include required files
    require_once 'config.php';
    require_once 'db_connect.php';
    require_once 'auth.php';
    require_once 'functions.php';
    
    // Sanitize input
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = sanitize($_POST['role'] ?? '');
    
    // Debug: Tampilkan input
    // echo "Username: $username<br>";
    // echo "Password: $password<br>";
    // echo "Role: $role<br>";
    
    // Validate input
    if (empty($username) || empty($password) || empty($role)) {
        $_SESSION['error'] = 'Harap isi semua field!';
        redirect('../login.php');
    }
    
    // Attempt login
    $user = $auth->login($username, $password, $role);
    
    if ($user) {
        // Debug: Tampilkan data user
        // echo "<pre>";
        // print_r($user);
        // echo "</pre>";
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;
        
        // Update last login
        $db->query('UPDATE users SET last_login = NOW() WHERE id = :id');
        $db->bind(':id', $user['id']);
        $db->execute();
        
        $_SESSION['success'] = 'Login berhasil! Selamat datang ' . $user['full_name'];
        
        // Redirect based on role
        if (in_array($user['role'], ['admin', 'manager'])) {
            redirect('../admin/index.php');
        } elseif ($user['role'] === 'user') {
            redirect('../customer/index.php');
        } else {
            // For other staff roles
            redirect('../customer/index.php?staff=true');
        }
    } else {
        $_SESSION['error'] = 'Username/password salah atau role tidak sesuai!';
        redirect('../login.php');
    }
} else {
    // If not POST request, redirect to login
    redirect('../login.php');
}
?>