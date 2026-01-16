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

// Get product data
$db->query('SELECT * FROM products WHERE id = :id');
$db->bind(':id', $product_id);
$product = $db->single();

if (!$product) {
    header('Location: products.php');
    exit();
}

// Get categories for dropdown
$db->query('SELECT * FROM categories ORDER BY name');
$categories = $db->resultSet();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_product'])) {
    $name = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $price = floatval($_POST['price']);
    $original_price = !empty($_POST['original_price']) ? floatval($_POST['original_price']) : null;
    $category_id = intval($_POST['category_id']);
    $stock = intval($_POST['stock']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // Handle image upload
    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/img/';
        $filename = uniqid() . '_' . basename($_FILES['image']['name']);
        $target_file = $upload_dir . $filename;
        
        // Check image type
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($imageFileType, $allowed_types)) {
            // Delete old image if exists
            if ($image && file_exists($upload_dir . $image)) {
                unlink($upload_dir . $image);
            }
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image = $filename;
            }
        }
    }
    
    // Update product
    $db->query('UPDATE products SET 
                name = :name,
                description = :description,
                price = :price,
                original_price = :original_price,
                category_id = :category_id,
                stock = :stock,
                image = :image,
                is_active = :is_active,
                updated_at = NOW()
                WHERE id = :id');
    
    $db->bind(':name', $name);
    $db->bind(':description', $description);
    $db->bind(':price', $price);
    $db->bind(':original_price', $original_price);
    $db->bind(':category_id', $category_id);
    $db->bind(':stock', $stock);
    $db->bind(':image', $image);
    $db->bind(':is_active', $is_active);
    $db->bind(':id', $product_id);
    
    if ($db->execute()) {
        $_SESSION['success'] = 'Product updated successfully!';
        header('Location: products.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update product. Please try again.';
    }
}

$page_title = 'Edit Product';
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
        }
        
        .sidebar {
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            min-height: 100vh;
            position: fixed;
            width: 250px;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 1000;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 20px;
            min-height: 100vh;
        }
        
        .content-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .form-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .image-preview {
            width: 200px;
            height: 200px;
            border: 2px dashed #ddd;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            margin: 0 auto 20px;
            cursor: pointer;
        }
        
        .image-preview img {
            max-width: 100%;
            max-height: 100%;
        }
        
        .current-image {
            margin-bottom: 20px;
        }
        
        .btn-gold {
            background: var(--accent-color);
            color: var(--primary-color);
            font-weight: 600;
            border: none;
        }
        
        .btn-gold:hover {
            background: #e6b800;
            color: var(--primary-color);
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
                    <h1>Edit Product</h1>
                    <a href="products.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Products
                    </a>
                </div>
                
                <?php displayMessage(); ?>
                
                <div class="form-card">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Product Name *</label>
                                    <input type="text" class="form-control" name="name" 
                                           value="<?php echo htmlspecialchars($product['name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Description *</label>
                                    <textarea class="form-control" name="description" rows="4" required><?php echo htmlspecialchars($product['description']); ?></textarea>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Price (Rp) *</label>
                                        <input type="number" class="form-control" name="price" 
                                               value="<?php echo $product['price']; ?>" step="0.01" min="0" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Original Price (Rp)</label>
                                        <input type="number" class="form-control" name="original_price" 
                                               value="<?php echo $product['original_price']; ?>" step="0.01" min="0">
                                        <small class="text-muted">Leave empty if no discount</small>
                                    </div>
                                </div>
                                
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Category *</label>
                                        <select class="form-select" name="category_id" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($categories as $category): ?>
                                            <option value="<?php echo $category['id']; ?>" 
                                                <?php echo $product['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($category['name']); ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Stock *</label>
                                        <input type="number" class="form-control" name="stock" 
                                               value="<?php echo $product['stock']; ?>" min="0" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" 
                                               <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="is_active">
                                            Product is active (visible to customers)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Right Column -->
                            <div class="col-md-6">
                                <div class="current-image mb-3">
                                    <label class="form-label">Current Image</label>
                                    <div class="text-center">
                                        <img src="<?php echo getProductImage($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>"
                                             class="img-thumbnail" style="max-height: 200px;">
                                        <p class="text-muted mt-2">Current product image</p>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Update Product Image</label>
                                    <div class="image-preview" id="imagePreview">
                                        <i class="fas fa-image"></i>
                                        <img id="previewImage" src="#" alt="Preview" style="display: none;">
                                    </div>
                                    <input type="file" class="form-control" name="image" id="imageInput" accept="image/*">
                                    <small class="text-muted">Leave empty to keep current image</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Product Information:</label>
                                    <ul class="text-muted">
                                        <li>Created: <?php echo date('M d, Y', strtotime($product['created_at'])); ?></li>
                                        <?php if ($product['updated_at']): ?>
                                        <li>Last Updated: <?php echo date('M d, Y', strtotime($product['updated_at'])); ?></li>
                                        <?php endif; ?>
                                        <li>Product ID: <?php echo $product['id']; ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" name="update_product" class="btn btn-gold btn-lg">
                                <i class="fas fa-save me-2"></i>Update Product
                            </button>
                            <a href="products.php" class="btn btn-secondary btn-lg ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Image preview functionality
        const imageInput = document.getElementById('imageInput');
        const imagePreview = document.getElementById('previewImage');
        const previewContainer = document.getElementById('imagePreview');
        
        imageInput.addEventListener('change', function() {
            const file = this.files[0];
            
            if (file) {
                const reader = new FileReader();
                
                reader.addEventListener('load', function() {
                    imagePreview.src = reader.result;
                    imagePreview.style.display = 'block';
                    previewContainer.querySelector('i').style.display = 'none';
                });
                
                reader.readAsDataURL(file);
            } else {
                imagePreview.style.display = 'none';
                previewContainer.querySelector('i').style.display = 'block';
            }
        });
        
        // Click on preview to trigger file input
        previewContainer.addEventListener('click', function() {
            imageInput.click();
        });
    </script>
</body>
</html>