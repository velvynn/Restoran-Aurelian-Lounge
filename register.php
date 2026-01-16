<?php session_start(); ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Elite Team - Restaurant Aurelian</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/login-style.css">
    <style>
        .registration-container {
            background-color: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 40px;
            width: 100%;
            max-width: 500px;
            margin: 0 auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .role-options {
            display: flex;
            gap: 20px;
            margin: 20px 0;
        }
        
        .role-option {
            flex: 1;
            background-color: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(212, 175, 55, 0.3);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .role-option:hover,
        .role-option.selected {
            background-color: rgba(212, 175, 55, 0.1);
            border-color: #d4af37;
        }
        
        .role-option i {
            font-size: 2.5rem;
            color: #d4af37;
            margin-bottom: 10px;
        }
        
        .role-option h3 {
            color: white;
            margin-bottom: 10px;
        }
        
        .role-option p {
            color: #ccc;
            font-size: 0.9rem;
        }
        
        .registration-form {
            display: none;
            margin-top: 30px;
        }
        
        .registration-form.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .back-button {
            background: none;
            border: none;
            color: #d4af37;
            font-size: 1rem;
            cursor: pointer;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .back-button:hover {
            color: #ffd369;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>RESTAURANT AURELIAN</h1>
            <div class="subtitle">Fine Dining Experience</div>
        </div>
        
        <div class="registration-container">
            <h2 style="text-align: center; color: #d4af37; margin-bottom: 30px;">Join Elite Team</h2>
            <p style="text-align: center; color: #ccc; margin-bottom: 30px;">Pilih tipe akun yang ingin Anda buat:</p>
            
            <div class="role-options">
                <div class="role-option" data-role="customer">
                    <i class="fas fa-user"></i>
                    <h3>Customer / Pelanggan</h3>
                    <p>Untuk memesan makanan online</p>
                </div>
                <div class="role-option" data-role="staff">
                    <i class="fas fa-utensils"></i>
                    <h3>Staff Restaurant</h3>
                    <p>Untuk karyawan restaurant</p>
                </div>
            </div>
            
            <!-- Form Registrasi Customer -->
            <div class="registration-form" id="customerForm">
                <button class="back-button" onclick="showRoleSelection()">
                    <i class="fas fa-arrow-left"></i> Kembali ke Pilihan
                </button>
                <h3 style="color: #d4af37; margin-bottom: 20px;">Registrasi Pelanggan</h3>
                <form action="includes/register_process.php" method="POST">
                    <input type="hidden" name="role" value="customer">
                    <div class="form-group">
                        <input type="text" name="full_name" class="form-control" placeholder="Nama Lengkap" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <input type="tel" name="phone" class="form-control" placeholder="Nomor Telepon" required>
                    </div>
                    <button type="submit" class="signin-button">Daftar sebagai Pelanggan</button>
                </form>
            </div>
            
            <!-- Form Registrasi Staff -->
            <div class="registration-form" id="staffForm">
                <button class="back-button" onclick="showRoleSelection()">
                    <i class="fas fa-arrow-left"></i> Kembali ke Pilihan
                </button>
                <h3 style="color: #d4af37; margin-bottom: 20px;">Registrasi Staff</h3>
                <form action="includes/register_process.php" method="POST">
                    <input type="hidden" name="role" value="staff">
                    <div class="form-group">
                        <input type="text" name="full_name" class="form-control" placeholder="Nama Lengkap" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="username" class="form-control" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="form-group">
                        <input type="text" name="staff_code" class="form-control" placeholder="Kode Referensi Staff" required>
                    </div>
                    <div class="form-group">
                        <select name="position" class="form-control" required>
                            <option value="">Pilih Posisi</option>
                            <option value="manager">Manager</option>
                            <option value="chef">Chef</option>
                            <option value="waiter">Waiter</option>
                            <option value="cashier">Cashier</option>
                            <option value="inventory">Inventory Staff</option>
                        </select>
                    </div>
                    <button type="submit" class="signin-button">Daftar sebagai Staff</button>
                </form>
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <p style="color: #ccc;">Sudah punya akun? <a href="login.php" style="color: #d4af37;">Login disini</a></p>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.role-option').forEach(option => {
            option.addEventListener('click', function() {
                const role = this.dataset.role;
                document.querySelectorAll('.role-option').forEach(o => o.classList.remove('selected'));
                this.classList.add('selected');
                
                document.querySelectorAll('.registration-form').forEach(form => {
                    form.classList.remove('active');
                });
                
                if (role === 'customer') {
                    document.getElementById('customerForm').classList.add('active');
                } else if (role === 'staff') {
                    document.getElementById('staffForm').classList.add('active');
                }
            });
        });
        
        function showRoleSelection() {
            document.querySelectorAll('.registration-form').forEach(form => {
                form.classList.remove('active');
            });
            document.querySelectorAll('.role-option').forEach(o => o.classList.remove('selected'));
        }
    </script>
</body>
</html>