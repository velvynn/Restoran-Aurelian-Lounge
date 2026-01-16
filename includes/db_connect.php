<?php
require_once 'config.php';

class Database {
    private $host = DB_HOST;
    private $port = DB_PORT;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    private $dbh;
    private $stmt;
    private $error;
    
    public function __construct() {
        // PERBAIKAN: HAPUS DEBUG OUTPUT INI
        // echo "<div style='background: #e3f2fd; padding: 10px; border-radius: 5px; margin: 10px;'>";
        // echo "<strong>🔄 Mencoba koneksi ke database...</strong><br>";
        // echo "Host: " . $this->host . "<br>";
        // echo "Port: " . $this->port . " (sesuai XAMPP Control Panel)<br>";
        // echo "Database: " . $this->dbname . "<br>";
        // echo "User: " . $this->user . "<br>";
        // echo "DSN: " . $dsn . "<br>";
        // echo "</div>";
        
        $dsn = 'mysql:host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->dbname . ';charset=utf8mb4';
        
        // Set options
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        );
        
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
            
            // PERBAIKAN: HAPUS DEBUG OUTPUT INI
            // echo "<div style='background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px;'>";
            // echo "✅ <strong>Koneksi database BERHASIL!</strong><br>";
            // echo "MySQL di XAMPP berjalan di port " . $this->port . "<br>";
            // echo "</div>";
            
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            // TETAP TAMPILKAN ERROR JIKA ADA MASALAH
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border: 1px solid #f5c6cb; border-radius: 5px; margin: 10px;'>";
            echo "❌ <strong>Database Connection Error:</strong><br>";
            echo "<strong>Pesan Error:</strong> " . $this->error . "<br>";
            echo "<strong>Host:</strong> " . $this->host . "<br>";
            echo "<strong>Port:</strong> " . $this->port . " (harusnya 3306 sesuai XAMPP)<br>";
            echo "<strong>Database:</strong> " . $this->dbname . "<br>";
            echo "<strong>User:</strong> " . $this->user . "<br>";
            echo "<strong>DSN yang digunakan:</strong> " . $dsn . "<br>";
            
            echo "<br><strong>🔧 Solusi:</strong><br>";
            echo "1. Pastikan MySQL di XAMPP sudah RUNNING (lihat lampu hijau)<br>";
            echo "2. Pastikan port MySQL di XAMPP adalah <strong>3306</strong><br>";
            echo "3. Buka XAMPP Control Panel → MySQL → Config → my.ini<br>";
            echo "4. Cari line: <code>port = 3306</code><br>";
            echo "5. Restart MySQL di XAMPP<br>";
            echo "6. Pastikan database '" . $this->dbname . "' sudah dibuat di phpMyAdmin<br>";
            echo "</div>";
            die();
        }
    }
    
    // Prepare statement with query
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }
    
    // Bind values
    public function bind($param, $value, $type = null) {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }
    
    // Execute the prepared statement
    public function execute() {
        try {
            return $this->stmt->execute();
        } catch(PDOException $e) {
            // PERBAIKAN: HAPUS ATAU COMMENT DEBUG OUTPUT
            // echo "<div style='background: #fff3cd; color: #856404; padding: 10px; border: 1px solid #ffeaa7; border-radius: 5px; margin: 5px;'>";
            // echo "⚠️ Execution Error: " . $e->getMessage();
            // echo "</div>";
            error_log("Database Execution Error: " . $e->getMessage()); // Simpan ke log saja
            return false;
        }
    }
    
    // Get result set as array of objects
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }
    
    // Get single record as object
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }
    
    // Get row count
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    // Get last inserted ID
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
    
    // Begin transaction
    public function beginTransaction() {
        return $this->dbh->beginTransaction();
    }
    
    // End transaction
    public function endTransaction() {
        return $this->dbh->commit();
    }
    
    // Cancel transaction
    public function cancelTransaction() {
        return $this->dbh->rollBack();
    }
}

// Buat instance database global
$database = new Database();
$db = $database;
?>