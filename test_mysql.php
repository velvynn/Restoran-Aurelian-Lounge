<?php
// File: test_mysql.php
// Letakkan di root folder (sama dengan index.php)

echo "<h2>🛠️ Testing MySQL Connection - XAMPP Port 3306</h2>";

$host = 'localhost';
$port = '3306'; // PORT MYSQL XAMPP ANDA
$dbname = 'restaurant_aurelian';
$username = 'root';
$password = '';

echo "<div style='background: #f0f8ff; padding: 15px; border-radius: 5px;'>";
echo "<strong>Konfigurasi:</strong><br>";
echo "Host: $host<br>";
echo "Port: $port (sesuai XAMPP Control Panel)<br>";
echo "Database: $dbname<br>";
echo "User: $username<br>";
echo "Password: " . (empty($password) ? "(kosong)" : "***") . "<br>";
echo "</div><br>";

try {
    // Coba koneksi tanpa database dulu
    echo "<strong>Step 1: Coba koneksi ke MySQL server...</strong><br>";
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Berhasil terhubung ke MySQL server di port $port<br><br>";
    
    // Cek versi MySQL
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    echo "✅ Versi MySQL: $version<br><br>";
    
    // Cek database
    echo "<strong>Step 2: Cek database '$dbname'...</strong><br>";
    $stmt = $pdo->query("SHOW DATABASES LIKE '$dbname'");
    
    if ($stmt->rowCount() > 0) {
        echo "✅ Database '$dbname' ditemukan<br>";
        
        // Hubungkan ke database
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
        
        // Cek tabel users
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabel 'users' ditemukan<br>";
            
            // Hitung user
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
            $result = $stmt->fetch();
            echo "✅ Jumlah user: " . $result['total'] . "<br>";
        } else {
            echo "❌ Tabel 'users' tidak ditemukan. Jalankan SQL untuk membuat tabel.<br>";
        }
        
    } else {
        echo "❌ Database '$dbname' tidak ditemukan.<br>";
        echo "<strong>Buat database dengan cara:</strong><br>";
        echo "1. Buka http://localhost:8080/phpmyadmin<br>";
        echo "2. Klik 'New'<br>";
        echo "3. Buat database dengan nama: <strong>$dbname</strong><br>";
        echo "4. Import file SQL yang sudah disediakan<br>";
    }
    
} catch(PDOException $e) {
    echo "<div style='background: #ffcccc; padding: 15px; border-radius: 5px;'>";
    echo "❌ <strong>Error:</strong> " . $e->getMessage() . "<br><br>";
    
    echo "<strong>💡 Solusi untuk XAMPP:</strong><br>";
    echo "1. Pastikan XAMPP Control Panel terbuka<br>";
    echo "2. Pastikan MySQL menunjukkan RUNNING (lampu hijau)<br>";
    echo "3. Port harus <strong>3306</strong> sesuai XAMPP Anda<br>";
    echo "4. Buka XAMPP → MySQL → Config → my.ini<br>";
    echo "5. Cari: <code>port = 3306</code><br>";
    echo "6. Restart MySQL<br>";
    echo "7. Buka phpMyAdmin di: http://localhost:8080/phpmyadmin<br>";
    echo "</div>";
}

echo "<br><hr>";
echo "<strong>Langkah selanjutnya:</strong><br>";
echo "1. Buka http://localhost:8080/phpmyadmin<br>";
echo "2. Buat database 'restaurant_aurelian'<br>";
echo "3. Import file SQL (jika ada)<br>";
echo "4. Test login dengan user default<br>";
?>