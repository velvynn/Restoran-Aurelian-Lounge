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

if (!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = intval($_GET['id']);

// Check if product exists
$db->query('SELECT * FROM products WHERE id = :id');
$db->bind(':id', $product_id);
$product = $db->single();

if (!$product) {
    $_SESSION['error'] = 'Product not found!';
    header('Location: products.php');
    exit();
}

// Delete product
$db->query('DELETE FROM products WHERE id = :id');
$db->bind(':id', $product_id);

if ($db->execute()) {
    // Delete product image if exists
    if ($product['image']) {
        $image_path = '../assets/img/' . $product['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    $_SESSION['success'] = 'Product deleted successfully!';
} else {
    $_SESSION['error'] = 'Failed to delete product. Please try again.';
}

header('Location: products.php');
exit();
?>