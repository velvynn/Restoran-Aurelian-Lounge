<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/functions.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}

$user_id = $_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);

if (isset($input['cart']) && is_array($input['cart'])) {
    // Clear existing cart items
    $db->query('DELETE FROM cart WHERE user_id = :user_id');
    $db->bind(':user_id', $user_id);
    $db->execute();
    
    // Add new cart items
    foreach ($input['cart'] as $item) {
        if (isset($item['id']) && isset($item['quantity']) && $item['quantity'] > 0) {
            $db->query('INSERT INTO cart (user_id, product_id, quantity) 
                       VALUES (:user_id, :product_id, :quantity)
                       ON DUPLICATE KEY UPDATE quantity = :quantity');
            $db->bind(':user_id', $user_id);
            $db->bind(':product_id', intval($item['id']));
            $db->bind(':quantity', intval($item['quantity']));
            $db->execute();
        }
    }
    
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid cart data']);
}
?>