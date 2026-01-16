<?php
// dashboard.php - Redirect ke halaman yang sesuai
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}

// Redirect berdasarkan role
if (isset($_SESSION['role'])) {
    if (in_array($_SESSION['role'], ['admin', 'manager'])) {
        header('Location: admin/index.php');
    } elseif ($_SESSION['role'] === 'user') {
        header('Location: customer/index.php');
    } else {
        header('Location: customer/index.php?staff=true');
    }
} else {
    header('Location: login.php');
}
exit();
?>