<?php
// Fungsi sanitize input
function sanitize($input) {
    if (is_array($input)) {
        foreach ($input as $key => $value) {
            $input[$key] = sanitize($value);
        }
    } else {
        $input = trim($input);
        $input = stripslashes($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
    return $input;
}

// Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']);
}

// Redirect user
function redirect($url) {
    if (!headers_sent()) {
        header('Location: ' . $url);
        exit();
    } else {
        echo '<script>window.location.href="' . $url . '";</script>';
        echo '<noscript><meta http-equiv="refresh" content="0;url=' . $url . '" /></noscript>';
        exit();
    }
}

// Format currency Indonesia
function formatCurrency($amount) {
    return 'Rp ' . number_format($amount, 0, ',', '.');
}

// Get category name by ID
function getCategoryName($category_id) {
    global $db;
    try {
        $db->query('SELECT name FROM categories WHERE id = :id');
        $db->bind(':id', $category_id);
        $result = $db->single();
        return $result ? $result['name'] : 'Uncategorized';
    } catch (Exception $e) {
        error_log("Error in getCategoryName: " . $e->getMessage());
        return 'Uncategorized';
    }
}

// Get product image URL - PERBAIKAN UTAMA
function getProductImage($image) {
    $default_image = SITE_URL . 'assets/img/default-product.jpg';

    // Jika kosong langsung pakai default
    if (empty($image) || trim($image) === '') {
        return $default_image;
    }

    $image = trim($image);

    // Jika sudah berupa URL penuh
    if (filter_var($image, FILTER_VALIDATE_URL)) {
        return $image;
    }

    // Ambil nama file saja
    $filename = basename($image);

    // Lokasi lokal yang dicek dulu supaya tidak kembali URL yang salah
    $locations = [
        [
            'path' => realpath(__DIR__ . '/../assets/img/' . $filename),
            'url'  => SITE_URL . 'assets/img/' . $filename,
        ],
        [
            'path' => realpath(__DIR__ . '/../assets/uploads/products/' . $filename),
            'url'  => SITE_URL . 'assets/uploads/products/' . $filename,
        ],
    ];

    foreach ($locations as $loc) {
        if ($loc['path'] && file_exists($loc['path'])) {
            return $loc['url'];
        }
    }

    // Jika gagal, coba kembalikan URL relatif yang mungkin tersimpan di DB
    if (strpos($image, 'assets/') === 0) {
        return SITE_URL . ltrim($image, '/');
    }

    // Fallback terakhir: default
    return $default_image;
}

// Fungsi bantu untuk debugging gambar
function debugImagePath($image) {
    echo "<!-- DEBUG IMAGE INFO -->\n";
    echo "<!-- Image from DB: " . htmlspecialchars($image) . " -->\n";
    echo "<!-- SITE_URL: " . SITE_URL . " -->\n";
    echo "<!-- Document Root: " . $_SERVER['DOCUMENT_ROOT'] . " -->\n";
    echo "<!-- Server Name: " . $_SERVER['SERVER_NAME'] . " -->\n";
    echo "<!-- Request URI: " . $_SERVER['REQUEST_URI'] . " -->\n";
    
    $test_paths = [
        SITE_URL . 'assets/img/' . $image,
        $_SERVER['DOCUMENT_ROOT'] . '/assets/img/' . $image,
        $_SERVER['DOCUMENT_ROOT'] . '/restaurant-aurelian/assets/img/' . $image,
    ];
    
    foreach ($test_paths as $i => $path) {
        echo "<!-- Path $i: " . $path . " -->\n";
        if (file_exists($path)) {
            echo "<!-- Path $i: EXISTS -->\n";
        } else {
            echo "<!-- Path $i: NOT FOUND -->\n";
        }
    }
    echo "<!-- END DEBUG -->\n";
}

// Generate order code
function generateOrderCode() {
    return 'ORD-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
}

// Get cart count for user
function getCartCount() {
    if (isset($_SESSION['user_id'])) {
        global $db;
        try {
            $db->query('SELECT SUM(quantity) as count FROM cart WHERE user_id = :user_id');
            $db->bind(':user_id', $_SESSION['user_id']);
            $result = $db->single();
            return $result['count'] ? intval($result['count']) : 0;
        } catch (Exception $e) {
            error_log("Error in getCartCount: " . $e->getMessage());
            return 0;
        }
    }
    return 0;
}

// Display flash messages - TAMBAHKAN PENANGANAN JIKA TIDAK ADA SESSION
function displayMessage() {
    if (!isset($_SESSION)) {
        session_start();
    }
    
    if (isset($_SESSION['success'])) {
        echo '<div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>
                ' . $_SESSION['success'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>';
        unset($_SESSION['success']);
    }
    
    if (isset($_SESSION['error'])) {
        echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>
                ' . $_SESSION['error'] . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        unset($_SESSION['error']);
    }
    
    if (isset($_SESSION['errors']) && is_array($_SESSION['errors'])) {
        foreach ($_SESSION['errors'] as $error) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    ' . $error . '
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                  </div>';
        }
        unset($_SESSION['errors']);
    }
}

// Check user role
function checkRole($allowed_roles) {
    if (!isset($_SESSION['role'])) {
        return false;
    }
    
    if (is_array($allowed_roles)) {
        return in_array($_SESSION['role'], $allowed_roles);
    }
    
    return $_SESSION['role'] === $allowed_roles;
}

// Get base URL
function base_url($path = '') {
    return rtrim(SITE_URL, '/') . '/' . ltrim($path, '/');
}

// Get cart items for user
function getCartItems($user_id) {
    global $db;
    try {
        $db->query('SELECT c.*, p.name, p.price, p.image, p.stock, p.description 
                    FROM cart c 
                    JOIN products p ON c.product_id = p.id 
                    WHERE c.user_id = :user_id 
                    ORDER BY c.created_at DESC');
        $db->bind(':user_id', $user_id);
        return $db->resultSet();
    } catch (Exception $e) {
        error_log("Error in getCartItems: " . $e->getMessage());
        return [];
    }
}

// Calculate cart totals
function calculateCartTotals($cart_items) {
    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }
    
    $tax = $subtotal * 0.10; // 10% tax
    $shipping = $subtotal > 0 ? 15000 : 0;
    $total = $subtotal + $tax + $shipping;
    
    return [
        'subtotal' => $subtotal,
        'tax' => $tax,
        'shipping' => $shipping,
        'total' => $total
    ];
}

// Check if product is in stock
function checkStock($product_id, $quantity = 1) {
    global $db;
    try {
        $db->query('SELECT stock FROM products WHERE id = :id AND is_active = 1');
        $db->bind(':id', $product_id);
        $result = $db->single();
        
        if ($result && $result['stock'] >= $quantity) {
            return true;
        }
        return false;
    } catch (Exception $e) {
        error_log("Error in checkStock: " . $e->getMessage());
        return false;
    }
}

// Update product stock after order
function updateProductStock($product_id, $quantity) {
    global $db;
    try {
        $db->query('UPDATE products SET stock = stock - :quantity WHERE id = :id');
        $db->bind(':quantity', $quantity);
        $db->bind(':id', $product_id);
        return $db->execute();
    } catch (Exception $e) {
        error_log("Error in updateProductStock: " . $e->getMessage());
        return false;
    }
}

// Get user orders with items
function getUserOrders($user_id) {
    global $db;
    try {
        $db->query('SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC');
        $db->bind(':user_id', $user_id);
        $orders = $db->resultSet();
        
        // Get order items for each order
        foreach ($orders as &$order) {
            $db->query('SELECT oi.*, p.name, p.image FROM order_items oi 
                        JOIN products p ON oi.product_id = p.id 
                        WHERE oi.order_id = :order_id');
            $db->bind(':order_id', $order['id']);
            $order['items'] = $db->resultSet();
        }
        
        return $orders;
    } catch (Exception $e) {
        error_log("Error in getUserOrders: " . $e->getMessage());
        return [];
    }
}

// Add product to cart
function addToCart($user_id, $product_id, $quantity = 1) {
    global $db;
    try {
        // Check if product exists and is active
        if (!checkStock($product_id, $quantity)) {
            return ['success' => false, 'error' => 'Product out of stock'];
        }
        
        // Check if already in cart
        $db->query('SELECT * FROM cart WHERE user_id = :user_id AND product_id = :product_id');
        $db->bind(':user_id', $user_id);
        $db->bind(':product_id', $product_id);
        $existing = $db->single();
        
        if ($existing) {
            // Update quantity
            $new_quantity = $existing['quantity'] + $quantity;
            $db->query('UPDATE cart SET quantity = :quantity WHERE id = :id');
            $db->bind(':quantity', $new_quantity);
            $db->bind(':id', $existing['id']);
            $db->execute();
        } else {
            // Add to cart
            $db->query('INSERT INTO cart (user_id, product_id, quantity) VALUES (:user_id, :product_id, :quantity)');
            $db->bind(':user_id', $user_id);
            $db->bind(':product_id', $product_id);
            $db->bind(':quantity', $quantity);
            $db->execute();
        }
        
        return ['success' => true];
    } catch (Exception $e) {
        error_log("Error in addToCart: " . $e->getMessage());
        return ['success' => false, 'error' => 'Failed to add to cart'];
    }
}

// Remove product from cart
function removeFromCart($user_id, $cart_item_id) {
    global $db;
    try {
        $db->query('DELETE FROM cart WHERE id = :id AND user_id = :user_id');
        $db->bind(':id', $cart_item_id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    } catch (Exception $e) {
        error_log("Error in removeFromCart: " . $e->getMessage());
        return false;
    }
}

// Clear user cart
function clearCart($user_id) {
    global $db;
    try {
        $db->query('DELETE FROM cart WHERE user_id = :user_id');
        $db->bind(':user_id', $user_id);
        return $db->execute();
    } catch (Exception $e) {
        error_log("Error in clearCart: " . $e->getMessage());
        return false;
    }
}

// Update cart item quantity
function updateCartQuantity($user_id, $cart_item_id, $quantity) {
    global $db;
    try {
        if ($quantity <= 0) {
            return removeFromCart($user_id, $cart_item_id);
        }
        
        // Check stock
        $db->query('SELECT product_id FROM cart WHERE id = :id AND user_id = :user_id');
        $db->bind(':id', $cart_item_id);
        $db->bind(':user_id', $user_id);
        $item = $db->single();
        
        if ($item && checkStock($item['product_id'], $quantity)) {
            $db->query('UPDATE cart SET quantity = :quantity WHERE id = :id AND user_id = :user_id');
            $db->bind(':quantity', $quantity);
            $db->bind(':id', $cart_item_id);
            $db->bind(':user_id', $user_id);
            return $db->execute();
        }
        return false;
    } catch (Exception $e) {
        error_log("Error in updateCartQuantity: " . $e->getMessage());
        return false;
    }
}

// Get order status badge HTML
function getOrderStatusBadge($status) {
    $status_classes = [
        'pending' => 'warning',
        'processing' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        'delivered' => 'success'
    ];
    
    $class = $status_classes[$status] ?? 'secondary';
    $text = ucfirst($status);
    
    return '<span class="badge bg-' . $class . '">' . $text . '</span>';
}

// Get payment status badge HTML
function getPaymentStatusBadge($status) {
    $status_classes = [
        'pending' => 'warning',
        'paid' => 'success',
        'failed' => 'danger'
    ];
    
    $class = $status_classes[$status] ?? 'secondary';
    $text = ucfirst($status);
    
    return '<span class="badge bg-' . $class . '">' . $text . '</span>';
}

// Validate email format
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

// Validate phone number (Indonesian format)
function isValidPhone($phone) {
    return preg_match('/^(\+62|62|0)8[1-9][0-9]{6,9}$/', $phone);
}

// Get current date time for MySQL
function getCurrentDateTime() {
    return date('Y-m-d H:i:s');
}

// Get time ago format
function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } else {
        return date('M d, Y', $time);
    }
}

// Fungsi alternatif yang lebih sederhana untuk gambar
function getSimpleImagePath($image) {
    if (empty($image)) {
        $image = 'default-product.jpg';
    }
    
    $base_url = SITE_URL;
    
    // Pastikan SITE_URL diakhiri dengan slash
    if (substr($base_url, -1) !== '/') {
        $base_url .= '/';
    }
    
    // Tambahkan 'restaurant-aurelian/' jika belum ada
    if (strpos($base_url, 'restaurant-aurelian') === false) {
        $base_url .= 'restaurant-aurelian/';
    }
    
    // Pastikan tidak ada double slash
    $base_url = rtrim($base_url, '/') . '/';
    
    // Return path lengkap
    return $base_url . 'assets/img/' . $image;
}

// Fungsi untuk membuat gambar fallback jika tidak ditemukan
function getProductImageWithFallback($image, $product_name = '') {
    $img_path = getProductImage($image);
    
    // Tambahkan atribut onerror untuk fallback
    $fallback_url = SITE_URL . 'assets/img/default-product.jpg';
    
    $html = '<img src="' . htmlspecialchars($img_path) . '" ';
    $html .= 'alt="' . htmlspecialchars($product_name) . '" ';
    $html .= 'onerror="this.onerror=null; this.src=\'' . $fallback_url . '\';" ';
    $html .= 'style="max-width: 100%; height: auto;">';
    
    return $html;
}
?>