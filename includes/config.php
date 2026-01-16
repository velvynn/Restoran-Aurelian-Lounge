<?php
// Konfigurasi error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Konfigurasi Database - SESUAI DENGAN XAMPP ANDA
define('DB_HOST', 'localhost');  // atau '127.0.0.1'
define('DB_PORT', '3306');       // PORT MYSQL DARI XAMPP: 3306
define('DB_USER', 'root');       // user default XAMPP
define('DB_PASS', '');           // password default XAMPP (kosong)
define('DB_NAME', 'restaurant_aurelian'); // pastikan database sudah dibuat

// Konfigurasi Situs - hitung otomatis path dasar (mendukung folder dengan spasi)
define('SITE_NAME', 'Aurelian Lounge');
define('SITE_EMAIL', 'info@aurelianlounge.com');
define('ADMIN_EMAIL', 'admin@aurelianlounge.com');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Ambil path relatif project terhadap document root, lalu URL-encode setiap segmen agar spasi tidak bermasalah
$doc_root = str_replace('\\', '/', rtrim($_SERVER['DOCUMENT_ROOT'], '/'));
$project_root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$relative_path = trim(str_replace($doc_root, '', $project_root), '/');
$encoded_path = $relative_path !== '' ? implode('/', array_map('rawurlencode', explode('/', $relative_path))) . '/' : '';

define('SITE_URL', $protocol . $host . '/' . $encoded_path);
?>