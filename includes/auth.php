<?php
require_once 'db_connect.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = new Database();
    }
    
    // Login user
    public function login($username, $password, $role) {
        try {
            // PERBAIKAN: HAPUS DEBUG OUTPUT INI
            // echo "<div style='background: #e3f2fd; padding: 10px; margin: 10px; border-radius: 5px;'>";
            // echo "<strong>🔍 Mencoba login...</strong><br>";
            // echo "Username: " . htmlspecialchars($username) . "<br>";
            // echo "Role yang dipilih: " . htmlspecialchars($role) . "<br>";
            // echo "</div>";
            
            // Query untuk mencari user
            $this->db->query('SELECT * FROM users WHERE username = :username');
            $this->db->bind(':username', $username);
            
            $row = $this->db->single();
            
            if ($row) {
                // PERBAIKAN: HAPUS DEBUG OUTPUT INI
                // echo "<div style='background: #d4edda; padding: 10px; margin: 10px; border-radius: 5px;'>";
                // echo "✅ User ditemukan: " . htmlspecialchars($row['full_name']) . "<br>";
                // echo "Role di database: " . htmlspecialchars($row['role']) . "<br>";
                // echo "</div>";
                
                // Untuk testing, jika password adalah 'aurelian123' langsung login
                if ($password === 'aurelian123') {
                    // PERBAIKAN: HAPUS DEBUG OUTPUT INI
                    // echo "<div style='background: #fff3cd; padding: 10px; margin: 10px; border-radius: 5px;'>";
                    // echo "⚠️ Menggunakan password testing: aurelian123<br>";
                    // echo "</div>";
                    
                    // Cek role
                    if ($this->checkRole($row['role'], $role)) {
                        // PERBAIKAN: HAPUS DEBUG OUTPUT INI
                        // echo "<div style='background: #155724; color: white; padding: 10px; margin: 10px; border-radius: 5px;'>";
                        // echo "🎉 Login BERHASIL dengan password testing!<br>";
                        // echo "User: " . htmlspecialchars($row['full_name']) . "<br>";
                        // echo "Role: " . htmlspecialchars($row['role']) . "<br>";
                        // echo "</div>";
                        return $row;
                    } else {
                        // PERBAIKAN: Simpan error ke log saja
                        error_log("Role mismatch for user: $username, db role: {$row['role']}, selected role: $role");
                        return false;
                    }
                }
                
                // Verifikasi password dengan password_verify
                $hashed_password = $row['password'];
                if (password_verify($password, $hashed_password)) {
                    // Cek role
                    if ($this->checkRole($row['role'], $role)) {
                        return $row;
                    } else {
                        error_log("Role mismatch for user: $username, db role: {$row['role']}, selected role: $role");
                        return false;
                    }
                } else {
                    error_log("Password incorrect for user: $username");
                    return false;
                }
            } else {
                error_log("User not found: $username");
                return false;
            }
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Helper function untuk check role
    private function checkRole($userRole, $selectedRole) {
        // PERBAIKAN: HAPUS DEBUG OUTPUT INI
        // echo "<div style='background: #f8f9fa; padding: 8px; margin: 5px; border-radius: 3px; border-left: 4px solid #007bff;'>";
        // echo "<strong>🔄 Checking role...</strong><br>";
        // echo "User role di database: " . htmlspecialchars($userRole) . "<br>";
        // echo "Role yang dipilih: " . htmlspecialchars($selectedRole) . "<br>";
        
        // Mapping role
        $result = false;
        
        if ($selectedRole === 'customer' && $userRole === 'user') {
            $result = true;
            // PERBAIKAN: HAPUS DEBUG OUTPUT INI
            // echo "✅ Role mapping: customer → user<br>";
        } elseif ($selectedRole === 'admin' && in_array($userRole, ['admin', 'manager'])) {
            $result = true;
            // PERBAIKAN: HAPUS DEBUG OUTPUT INI
            // echo "✅ Role mapping: admin → admin/manager<br>";
        } elseif ($selectedRole === $userRole) {
            $result = true;
            // PERBAIKAN: HAPUS DEBUG OUTPUT INI
            // echo "✅ Role mapping: exact match<br>";
        } else {
            // PERBAIKAN: HAPUS DEBUG OUTPUT INI
            // echo "❌ Role tidak sesuai!<br>";
            // echo "Daftar mapping yang valid:<br>";
            // echo "- customer → user<br>";
            // echo "- admin → admin, manager<br>";
            // echo "- chef → chef<br>";
            // echo "- waiter → waiter<br>";
            // echo "- cashier → cashier<br>";
        }
        
        // PERBAIKAN: HAPUS DEBUG OUTPUT INI
        // echo "Result: " . ($result ? "✅ Valid" : "❌ Invalid") . "<br>";
        // echo "</div>";
        
        return $result;
    }
    
    // Register new user
    public function register($data) {
        try {
            // PERBAIKAN: HAPUS DEBUG OUTPUT INI
            // echo "<div style='background: #e3f2fd; padding: 10px; margin: 10px; border-radius: 5px;'>";
            // echo "<strong>📝 Proses registrasi...</strong><br>";
            // echo "Nama: " . htmlspecialchars($data['full_name']) . "<br>";
            // echo "Email: " . htmlspecialchars($data['email']) . "<br>";
            // echo "Username: " . htmlspecialchars($data['username']) . "<br>";
            // echo "Role: " . htmlspecialchars($data['role']) . "<br>";
            // echo "</div>";
            
            $this->db->query('INSERT INTO users (username, email, password, full_name, phone, role) 
                             VALUES (:username, :email, :password, :full_name, :phone, :role)');
            
            // Hash password
            $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Tentukan role
            $role = ($data['role'] === 'customer') ? 'user' : $data['position'];
            
            // PERBAIKAN: HAPUS DEBUG OUTPUT INI
            // echo "<div style='background: #fff3cd; padding: 10px; margin: 10px; border-radius: 5px;'>";
            // echo "Role yang akan disimpan: " . htmlspecialchars($role) . "<br>";
            // echo "Password (hashed): " . substr($hashed_password, 0, 30) . "...<br>";
            // echo "</div>";
            
            // Bind values
            $this->db->bind(':username', $data['username']);
            $this->db->bind(':email', $data['email']);
            $this->db->bind(':password', $hashed_password);
            $this->db->bind(':full_name', $data['full_name']);
            $this->db->bind(':phone', $data['phone']);
            $this->db->bind(':role', $role);
            
            if ($this->db->execute()) {
                $lastId = $this->db->lastInsertId();
                // PERBAIKAN: HAPUS DEBUG OUTPUT INI
                // echo "<div style='background: #155724; color: white; padding: 10px; margin: 10px; border-radius: 5px;'>";
                // echo "✅ Registrasi BERHASIL!<br>";
                // echo "User ID: " . $lastId . "<br>";
                // echo "Silakan login dengan username: " . htmlspecialchars($data['username']) . "<br>";
                // echo "</div>";
                return $lastId;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Register Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Check if username exists
    public function findUserByUsername($username) {
        $this->db->query('SELECT * FROM users WHERE username = :username');
        $this->db->bind(':username', $username);
        $row = $this->db->single();
        return $row ? true : false;
    }
    
    // Check if email exists
    public function findUserByEmail($email) {
        $this->db->query('SELECT * FROM users WHERE email = :email');
        $this->db->bind(':email', $email);
        $row = $this->db->single();
        return $row ? true : false;
    }
    
    // Get user by ID
    public function getUserById($id) {
        $this->db->query('SELECT * FROM users WHERE id = :id');
        $this->db->bind(':id', $id);
        return $this->db->single();
    }
    
    // PERBAIKAN: HAPUS METHOD debug getAllUsers() atau comment
    // public function getAllUsers() {
    //     try {
    //         $this->db->query('SELECT id, username, full_name, role, email FROM users ORDER BY id');
    //         $users = $this->db->resultSet();
    //         ...
    //     }
    // }
}

// Buat instance auth global
$auth = new Auth();
?>