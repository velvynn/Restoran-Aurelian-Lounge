<?php
// Manual image mapper - lihat dan edit gambar produk satu per satu
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['new_image'])) {
    $product_id = intval($_POST['product_id']);
    $new_image = trim($_POST['new_image']);
    
    $db->query('UPDATE products SET image = :image WHERE id = :id');
    $db->bind(':image', $new_image);
    $db->bind(':id', $product_id);
    $db->execute();
    
    $_SESSION['message'] = "Updated product ID $product_id with image: $new_image";
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get all products
$db->query('SELECT id, name, image FROM products ORDER BY id');
$products = $db->resultSet();

// Get all available images
$img_dir = realpath(__DIR__ . '/../assets/img');
$available_images = [];
if (is_dir($img_dir)) {
    foreach (scandir($img_dir) as $file) {
        if ($file !== '.' && $file !== '..' && is_file($img_dir . '/' . $file)) {
            $available_images[] = $file;
        }
    }
}
sort($available_images);

header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manual Image Mapper</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 20px; 
            background: #f5f5f5;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
            background: white; 
            padding: 20px; 
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        h2 { 
            color: #0b3b2e;
            border-bottom: 3px solid #d4af37;
            padding-bottom: 10px;
        }
        .product-row {
            display: grid;
            grid-template-columns: 80px 150px 200px 1fr 150px;
            gap: 15px;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            margin-bottom: 10px;
        }
        .product-row:hover {
            background: #f9f9f9;
        }
        .product-id { font-weight: bold; color: #0b3b2e; }
        .product-name { font-weight: 500; }
        .current-img {
            padding: 8px;
            background: #f0f0f0;
            border-radius: 4px;
            font-size: 12px;
            color: #666;
            word-break: break-all;
        }
        .img-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
        }
        select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 12px;
        }
        .btn {
            padding: 8px 16px;
            background: #0b3b2e;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
        }
        .btn:hover {
            background: #0f5132;
        }
        .message {
            padding: 12px;
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .header {
            display: grid;
            grid-template-columns: 80px 150px 200px 1fr 150px;
            gap: 15px;
            font-weight: bold;
            color: #0b3b2e;
            padding: 10px 15px;
            background: #f0f0f0;
            border-radius: 4px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🖼️ Manual Image Mapper</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message">✓ <?php echo htmlspecialchars($_SESSION['message']); ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>

        <p><strong>Total Products:</strong> <?php echo count($products); ?> | <strong>Available Images:</strong> <?php echo count($available_images); ?></p>

        <div class="header">
            <div>ID</div>
            <div>Product</div>
            <div>Current Image</div>
            <div>Preview</div>
            <div>New Image</div>
        </div>

        <?php foreach ($products as $p): ?>
            <?php
            $current_img = trim($p['image'] ?? '');
            $img_exists = file_exists($img_dir . '/' . $current_img);
            $preview_url = $img_exists ? 
                SITE_URL . 'assets/img/' . rawurlencode($current_img) : 
                SITE_URL . 'assets/img/default-product.jpg';
            ?>
            <form method="POST">
                <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                
                <div class="product-row">
                    <div class="product-id">ID <?php echo $p['id']; ?></div>
                    <div class="product-name"><?php echo htmlspecialchars($p['name']); ?></div>
                    <div class="current-img">
                        <?php echo $current_img ?: '(empty)'; ?>
                        <?php if (!$img_exists && $current_img): ?>
                            <br><span style="color: red;">❌ MISSING</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <img src="<?php echo $preview_url; ?>" alt="Preview" class="img-preview">
                    </div>
                    <div>
                        <select name="new_image" required>
                            <option value="">-- Choose Image --</option>
                            <?php foreach ($available_images as $img): ?>
                                <option value="<?php echo $img; ?>" <?php echo $current_img === $img ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($img); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn" style="width: 100%; margin-top: 5px;">Update</button>
                    </div>
                </div>
            </form>
        <?php endforeach; ?>

        <hr style="margin: 30px 0;">
        <a href="../customer/menu.php" class="btn">← Back to Menu</a>
    </div>
</body>
</html>