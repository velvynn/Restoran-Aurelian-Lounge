<?php
// Script untuk mengecek struktur gambar
echo "<h1>Checking Image Structure</h1>";
echo "<pre>";

// Cek SITE_URL
echo "SITE_URL: " . (defined('SITE_URL') ? SITE_URL : 'NOT DEFINED') . "\n";

// Cek document root
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";

// Cek apakah folder assets/img ada
$assets_path = $_SERVER['DOCUMENT_ROOT'] . '/restaurant-aurelian/assets/img/';
echo "Assets Path: " . $assets_path . "\n";
echo "Exists: " . (file_exists($assets_path) ? "YES" : "NO") . "\n";

// List semua file di folder img
if (file_exists($assets_path)) {
    echo "\nFiles in assets/img:\n";
    $files = scandir($assets_path);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- " . $file . "\n";
        }
    }
}

// Contoh nama gambar dari database
$sample_images = ['img4.jpg', 'img5.jpg', 'sandwich.jpg', 'lemon-tea.jpg'];
echo "\n\nChecking sample images:\n";
foreach ($sample_images as $image) {
    $full_path = $assets_path . $image;
    echo $image . ": " . (file_exists($full_path) ? "EXISTS" : "NOT FOUND") . "\n";
}

echo "</pre>";
?>