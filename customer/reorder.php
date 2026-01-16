<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = intval($_POST['order_id']);

// Get order items
$db->query('SELECT * FROM order_items WHERE order_id = :order_id');
$db->bind(':order_id', $order_id);
$items = $db->resultSet();

if (empty($items)) {
    echo json_encode(['success' => false, 'error' => 'No items found']);
    exit();
}

// Add items to cart
foreach ($items as $item) {
    // Check if product exists and is active
    $db->query('SELECT id FROM products WHERE id = :id AND is_active = 1');
    $db->bind(':id', $item['product_id']);
    $product = $db->single();
    
    if ($product) {
        // Check if already in cart
        $db->query('SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id');
        $db->bind(':user_id', $user_id);
        $db->bind(':product_id', $item['product_id']);
        $existing = $db->single();
        
        if ($existing) {
            // Update quantity
            $new_quantity = $existing['quantity'] + $item['quantity'];
            $db->query('UPDATE cart SET quantity = :quantity WHERE id = :id');
            $db->bind(':quantity', $new_quantity);
            $db->bind(':id', $existing['id']);
            $db->execute();
        } else {
            // Add to cart
            $db->query('INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)');
            $db->bind(':user_id', $user_id);
            $db->bind(':product_id', $item['product_id']);
            $db->bind(':quantity', $item['quantity']);
            $db->execute();
        }
    }
}

echo json_encode(['success' => true]);
?>