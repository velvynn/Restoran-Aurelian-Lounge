<?php
session_start();
require_once 'config.php';
require_once 'db_connect.php';
require_once 'auth.php';
require_once 'functions.php'; // <-- TAMBAHKAN INI

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = sanitize($_POST['full_name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $phone = sanitize($_POST['phone'] ?? '');
    $role = sanitize($_POST['role'] ?? '');
    $staff_code = isset($_POST['staff_code']) ? sanitize($_POST['staff_code']) : '';
    $position = isset($_POST['position']) ? sanitize($_POST['position']) : '';
    
    // Validate input
    $errors = [];
    
    if (empty($full_name) || empty($email) || empty($username) || empty($password) || empty($phone)) {
        $errors[] = 'Semua field wajib diisi!';
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Format email tidak valid!';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'Password minimal 6 karakter!';
    }
    
    // Check if username exists
    if ($auth->findUserByUsername($username)) {
        $errors[] = 'Username sudah digunakan!';
    }
    
    // Check if email exists
    if ($auth->findUserByEmail($email)) {
        $errors[] = 'Email sudah terdaftar!';
    }
    
    // For staff registration, check staff code
    if ($role === 'staff' && $staff_code !== 'AURELIAN2024') {
        $errors[] = 'Kode referensi staff tidak valid!';
    }
    
    if (empty($errors)) {
        $data = [
            'full_name' => $full_name,
            'email' => $email,
            'username' => $username,
            'password' => $password,
            'phone' => $phone,
            'role' => $role,
            'position' => $position
        ];
        
        $user_id = $auth->register($data);
        
        if ($user_id) {
            $_SESSION['success'] = 'Registrasi berhasil! Silakan login.';
            header('Location: ../login.php');
            exit();
        } else {
            $_SESSION['error'] = 'Terjadi kesalahan saat registrasi.';
            header('Location: ../register.php');
            exit();
        }
    } else {
        $_SESSION['errors'] = $errors;
        header('Location: ../register.php');
        exit();
    }
} else {
    header('Location: ../register.php');
    exit();
}
?>