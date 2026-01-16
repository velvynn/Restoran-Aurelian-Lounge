<?php
// index.php - File utama untuk redirect
session_start();

// Jika sudah login, redirect ke dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in'])) {
    if (isset($_SESSION['role'])) {
        if (in_array($_SESSION['role'], ['admin', 'manager'])) {
            header('Location: admin/index.php');
        } else {
            header('Location: customer/index.php');
        }
    } else {
        header('Location: login.php');
    }
} else {
    // Jika belum login, redirect ke login
    header('Location: login.php');
}
exit();
?>