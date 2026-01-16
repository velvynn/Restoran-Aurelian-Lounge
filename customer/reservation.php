<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in'])) {
    header('Location: ../login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['make_reservation'])) {
        $customer_name = sanitize($_POST['customer_name']);
        $customer_email = sanitize($_POST['customer_email']);
        $customer_phone = sanitize($_POST['customer_phone']);
        $reservation_date = sanitize($_POST['reservation_date']);
        $reservation_time = sanitize($_POST['reservation_time']);
        $number_of_people = intval($_POST['number_of_people']);
        $table_type = sanitize($_POST['table_type'] ?? 'regular'); // Default jika tidak ada
        $special_request = sanitize($_POST['special_request'] ?? '');
        
        // Validasi
        $errors = [];
        
        if (empty($customer_name) || empty($customer_email) || empty($customer_phone)) {
            $errors[] = 'Harap isi data pribadi lengkap!';
        }
        
        if (!filter_var($customer_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Format email tidak valid!';
        }
        
        if (empty($reservation_date) || empty($reservation_time)) {
            $errors[] = 'Harap pilih tanggal dan waktu reservasi!';
        }
        
        if ($number_of_people < 1 || $number_of_people > 20) {
            $errors[] = 'Jumlah tamu harus antara 1-20 orang!';
        }
        
        // Cek tanggal tidak boleh di masa lalu
        $selected_datetime = strtotime($reservation_date . ' ' . $reservation_time);
        if ($selected_datetime < time()) {
            $errors[] = 'Tanggal reservasi tidak boleh di masa lalu!';
        }
        
        if (empty($errors)) {
            try {
                $db->query('INSERT INTO reservations 
                           (user_id, customer_name, customer_email, customer_phone, 
                            reservation_date, reservation_time, number_of_people, 
                            table_type, special_request, status) 
                           VALUES (:user_id, :customer_name, :customer_email, :customer_phone, 
                                   :reservation_date, :reservation_time, :number_of_people, 
                                   :table_type, :special_request, :status)');
                
                $db->bind(':user_id', $user_id);
                $db->bind(':customer_name', $customer_name);
                $db->bind(':customer_email', $customer_email);
                $db->bind(':customer_phone', $customer_phone);
                $db->bind(':reservation_date', $reservation_date);
                $db->bind(':reservation_time', $reservation_time);
                $db->bind(':number_of_people', $number_of_people);
                $db->bind(':table_type', $table_type);
                $db->bind(':special_request', $special_request);
                $db->bind(':status', 'pending');
                
                if ($db->execute()) {
                    $_SESSION['success'] = 'Reservasi berhasil dibuat! Kami akan mengkonfirmasi via email/telepon.';
                    header('Location: reservation.php');
                    exit();
                }
            } catch (Exception $e) {
                $_SESSION['error'] = 'Terjadi kesalahan saat membuat reservasi: ' . $e->getMessage();
            }
        } else {
            $_SESSION['errors'] = $errors;
        }
    }
}

// Get user reservations
try {
    $db->query('SELECT * FROM reservations WHERE user_id = :user_id ORDER BY reservation_date DESC, reservation_time DESC');
    $db->bind(':user_id', $user_id);
    $reservations = $db->resultSet();
} catch (Exception $e) {
    $reservations = [];
    error_log("Error getting reservations: " . $e->getMessage());
}

$page_title = 'Reservasi Meja';
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
    
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <style>
        :root {
            --primary-color: #0b3b2e;
            --secondary-color: #0f5132;
            --accent-color: #d4af37;
            --light-color: #f8f9fa;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: white !important;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
        }
        
        .nav-link:hover, .nav-link.active {
            color: var(--accent-color) !important;
        }
        
        .container-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .page-header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border-left: 5px solid var(--accent-color);
        }
        
        .page-header h1 {
            color: var(--primary-color);
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .page-header p {
            color: #666;
            font-size: 1.1rem;
        }
        
        /* Reservation Form Styles */
        .reservation-form-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .form-section {
            margin-bottom: 25px;
            padding-bottom: 25px;
            border-bottom: 1px solid #eee;
        }
        
        .form-section:last-child {
            border-bottom: none;
        }
        
        .section-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 20px;
            font-size: 1.2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-title i {
            color: var(--accent-color);
        }
        
        .form-label {
            font-weight: 600;
            color: #444;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1);
        }
        
        .table-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }
        
        .table-option {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .table-option:hover {
            border-color: var(--accent-color);
            transform: translateY(-2px);
        }
        
        .table-option.selected {
            border-color: var(--accent-color);
            background: rgba(212, 175, 55, 0.05);
        }
        
        .table-icon {
            font-size: 24px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .table-name {
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .table-capacity {
            color: #666;
            font-size: 0.9rem;
        }
        
        .btn-reserve {
            background: linear-gradient(135deg, var(--accent-color) 0%, #ffd700 100%);
            color: var(--primary-color);
            padding: 15px 40px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1.1rem;
            transition: all 0.3s;
            width: 100%;
            margin-top: 20px;
        }
        
        .btn-reserve:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(212, 175, 55, 0.3);
        }
        
        /* Reservations List */
        .reservations-container {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .reservation-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .reservation-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .reservation-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .reservation-id {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        
        .status-badge {
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-pending { background: #FFF3CD; color: #856404; }
        .status-confirmed { background: #D4EDDA; color: #155724; }
        .status-cancelled { background: #F8D7DA; color: #721C24; }
        .status-completed { background: #D1ECF1; color: #0C5460; }
        
        .reservation-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .detail-item {
            margin-bottom: 15px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .detail-label i {
            color: var(--accent-color);
        }
        
        .detail-value {
            font-size: 1.1rem;
            font-weight: 500;
            color: #333;
        }
        
        .table-badge {
            display: inline-block;
            padding: 5px 15px;
            background: rgba(11, 59, 46, 0.1);
            color: var(--primary-color);
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }
        
        .empty-state i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #666;
            margin-bottom: 10px;
        }
        
        /* Calendar picker customization */
        .flatpickr-calendar {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
        }
        
        .flatpickr-day.selected {
            background: var(--accent-color);
            border-color: var(--accent-color);
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .container-main {
                padding: 10px;
            }
            
            .page-header, 
            .reservation-form-container,
            .reservations-container {
                padding: 20px;
            }
            
            .reservation-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .reservation-details {
                grid-template-columns: 1fr;
            }
            
            .table-options {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .table-options {
                grid-template-columns: 1fr;
            }
            
            .reservation-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="menu.php"><i class="fas fa-utensils me-1"></i> Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reservation.php"><i class="fas fa-calendar-alt me-1"></i> Reservasi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php"><i class="fas fa-shopping-cart me-1"></i> Pesanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php"><i class="fas fa-user me-1"></i> Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php"><i class="fas fa-sign-out-alt me-1"></i> Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-main">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-calendar-alt me-3"></i>Reservasi Meja</h1>
            <p>Pesan meja untuk pengalaman bersantap yang lebih baik di <?php echo SITE_NAME; ?></p>
        </div>
        
        <!-- Display Messages -->
        <?php displayMessage(); ?>
        
        <!-- Reservation Form -->
        <div class="reservation-form-container">
            <h2 class="section-title mb-4"><i class="fas fa-edit"></i> Buat Reservasi Baru</h2>
            
            <form method="POST" id="reservationForm">
                <!-- Personal Information -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-user"></i> Data Pribadi</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" class="form-control" name="customer_name" 
                                   value="<?php echo $_SESSION['full_name'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email *</label>
                            <input type="email" class="form-control" name="customer_email" 
                                   value="<?php echo $_SESSION['email'] ?? ''; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon *</label>
                            <input type="tel" class="form-control" name="customer_phone" 
                                   value="<?php echo $_SESSION['phone'] ?? ''; ?>" required>
                        </div>
                    </div>
                </div>
                
                <!-- Reservation Details -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-clock"></i> Detail Reservasi</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal *</label>
                            <input type="text" class="form-control datepicker" name="reservation_date" 
                                   placeholder="Pilih tanggal" required readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Waktu *</label>
                            <select class="form-select" name="reservation_time" required>
                                <option value="">Pilih waktu</option>
                                <option value="11:00">11:00 - Makan Siang</option>
                                <option value="12:00">12:00 - Makan Siang</option>
                                <option value="13:00">13:00 - Makan Siang</option>
                                <option value="14:00">14:00 - Makan Siang</option>
                                <option value="17:00">17:00 - Makan Malam</option>
                                <option value="18:00">18:00 - Makan Malam</option>
                                <option value="19:00">19:00 - Makan Malam</option>
                                <option value="20:00">20:00 - Makan Malam</option>
                                <option value="21:00">21:00 - Makan Malam</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah Tamu *</label>
                            <select class="form-select" name="number_of_people" required>
                                <option value="">Pilih jumlah tamu</option>
                                <?php for($i = 1; $i <= 20; $i++): ?>
                                <option value="<?php echo $i; ?>"><?php echo $i; ?> orang</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Table Selection -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-chair"></i> Pilih Tipe Meja</h3>
                    <div class="table-options">
                        <div class="table-option" data-type="regular">
                            <div class="table-icon"><i class="fas fa-square"></i></div>
                            <div class="table-name">Meja Reguler</div>
                            <div class="table-capacity">2-4 orang</div>
                            <input type="radio" name="table_type" value="regular" checked style="display: none;">
                        </div>
                        <div class="table-option" data-type="family">
                            <div class="table-icon"><i class="fas fa-users"></i></div>
                            <div class="table-name">Meja Keluarga</div>
                            <div class="table-capacity">4-6 orang</div>
                            <input type="radio" name="table_type" value="family" style="display: none;">
                        </div>
                        <div class="table-option" data-type="window">
                            <div class="table-icon"><i class="fas fa-window-maximize"></i></div>
                            <div class="table-name">Meja Jendela</div>
                            <div class="table-capacity">2-4 orang</div>
                            <input type="radio" name="table_type" value="window" style="display: none;">
                        </div>
                        <div class="table-option" data-type="vip">
                            <div class="table-icon"><i class="fas fa-crown"></i></div>
                            <div class="table-name">Meja VIP</div>
                            <div class="table-capacity">2-6 orang</div>
                            <input type="radio" name="table_type" value="vip" style="display: none;">
                        </div>
                    </div>
                </div>
                
                <!-- Special Request -->
                <div class="form-section">
                    <h3 class="section-title"><i class="fas fa-sticky-note"></i> Permintaan Khusus</h3>
                    <div class="mb-3">
                        <textarea class="form-control" name="special_request" rows="4" 
                                  placeholder="Contoh: Meja dekat jendela, tidak pedas, ulang tahun, dll."></textarea>
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" name="make_reservation" class="btn-reserve">
                    <i class="fas fa-calendar-check me-2"></i> BUAT RESERVASI SEKARANG
                </button>
            </form>
        </div>
        
        <!-- My Reservations -->
        <div class="reservations-container mt-5">
            <h2 class="section-title mb-4"><i class="fas fa-history"></i> Riwayat Reservasi Saya</h2>
            
            <?php if (empty($reservations)): ?>
                <div class="empty-state">
                    <i class="far fa-calendar-times"></i>
                    <h3>Belum ada reservasi</h3>
                    <p>Buat reservasi pertama Anda dengan mengisi form di atas</p>
                </div>
            <?php else: ?>
                <?php foreach ($reservations as $reservation): ?>
                <div class="reservation-card">
                    <div class="reservation-header">
                        <div class="reservation-id">
                            Booking #<?php echo str_pad($reservation['id'], 4, '0', STR_PAD_LEFT); ?>
                        </div>
                        <div class="status-badge status-<?php echo $reservation['status']; ?>">
                            <?php echo strtoupper($reservation['status']); ?>
                        </div>
                    </div>
                    
                    <div class="reservation-details">
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-user"></i> Nama</div>
                            <div class="detail-value"><?php echo htmlspecialchars($reservation['customer_name']); ?></div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-calendar-day"></i> Tanggal</div>
                            <div class="detail-value">
                                <?php echo date('F d, Y', strtotime($reservation['reservation_date'])); ?>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-clock"></i> Waktu</div>
                            <div class="detail-value">
                                <?php echo date('g:i A', strtotime($reservation['reservation_time'])); ?>
                            </div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-users"></i> Jumlah Tamu</div>
                            <div class="detail-value"><?php echo $reservation['number_of_people']; ?> orang</div>
                        </div>
                        
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-chair"></i> Tipe Meja</div>
                            <div class="detail-value">
                                <span class="table-badge">
                                    <?php 
                                    $table_types = [
                                        'regular' => 'Meja Reguler',
                                        'family' => 'Meja Keluarga', 
                                        'window' => 'Meja Jendela',
                                        'vip' => 'Meja VIP'
                                    ];
                                    echo $table_types[$reservation['table_type']] ?? 'Reguler';
                                    ?>
                                </span>
                            </div>
                        </div>
                        
                        <?php if (!empty($reservation['special_request'])): ?>
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-sticky-note"></i> Permintaan Khusus</div>
                            <div class="detail-value"><?php echo htmlspecialchars($reservation['special_request']); ?></div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="detail-item">
                            <div class="detail-label"><i class="fas fa-phone"></i> Kontak</div>
                            <div class="detail-value">
                                <?php echo htmlspecialchars($reservation['customer_phone']); ?><br>
                                <small><?php echo htmlspecialchars($reservation['customer_email']); ?></small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3 pt-3 border-top">
                        <small class="text-muted">
                            <i class="far fa-clock me-1"></i>Dibuat: 
                            <?php echo date('d/m/Y H:i', strtotime($reservation['created_at'])); ?>
                        </small>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
    
    <script>
        // Initialize date picker
        flatpickr(".datepicker", {
            dateFormat: "Y-m-d",
            minDate: "today",
            locale: "id",
            disableMobile: true
        });
        
        // Table selection
        document.querySelectorAll('.table-option').forEach(option => {
            option.addEventListener('click', function() {
                // Remove selected class from all options
                document.querySelectorAll('.table-option').forEach(opt => {
                    opt.classList.remove('selected');
                });
                
                // Add selected class to clicked option
                this.classList.add('selected');
                
                // Update radio button
                const radio = this.querySelector('input[type="radio"]');
                if (radio) {
                    radio.checked = true;
                }
            });
        });
        
        // Set default selected table
        document.querySelector('.table-option[data-type="regular"]').classList.add('selected');
        
        // Form validation
        document.getElementById('reservationForm').addEventListener('submit', function(e) {
            const date = this.querySelector('[name="reservation_date"]').value;
            const time = this.querySelector('[name="reservation_time"]').value;
            const guests = this.querySelector('[name="number_of_people"]').value;
            
            if (!date || !time || !guests) {
                e.preventDefault();
                alert('Harap lengkapi semua field yang wajib diisi!');
                return false;
            }
            
            return true;
        });
        
        // Auto-fill time slots based on selected date
        const timeSelect = document.querySelector('[name="reservation_time"]');
        const dateInput = document.querySelector('[name="reservation_date"]');
        
        dateInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value);
            const dayOfWeek = selectedDate.getDay(); // 0 = Sunday, 6 = Saturday
            
            // Clear current options
            timeSelect.innerHTML = '<option value="">Pilih waktu</option>';
            
            // Define time slots based on day
            let timeSlots = [];
            
            if (dayOfWeek >= 1 && dayOfWeek <= 5) { // Weekdays
                timeSlots = [
                    { time: '11:00', label: '11:00 - Makan Siang' },
                    { time: '12:00', label: '12:00 - Makan Siang' },
                    { time: '13:00', label: '13:00 - Makan Siang' },
                    { time: '14:00', label: '14:00 - Makan Siang' },
                    { time: '17:00', label: '17:00 - Makan Malam' },
                    { time: '18:00', label: '18:00 - Makan Malam' },
                    { time: '19:00', label: '19:00 - Makan Malam' },
                    { time: '20:00', label: '20:00 - Makan Malam' },
                    { time: '21:00', label: '21:00 - Makan Malam' }
                ];
            } else { // Weekend
                timeSlots = [
                    { time: '10:00', label: '10:00 - Brunch' },
                    { time: '11:00', label: '11:00 - Brunch' },
                    { time: '12:00', label: '12:00 - Makan Siang' },
                    { time: '13:00', label: '13:00 - Makan Siang' },
                    { time: '14:00', label: '14:00 - Makan Siang' },
                    { time: '15:00', label: '15:00 - Makan Siang' },
                    { time: '17:00', label: '17:00 - Makan Malam' },
                    { time: '18:00', label: '18:00 - Makan Malam' },
                    { time: '19:00', label: '19:00 - Makan Malam' },
                    { time: '20:00', label: '20:00 - Makan Malam' },
                    { time: '21:00', label: '21:00 - Makan Malam' }
                ];
            }
            
            // Add time slots to select
            timeSlots.forEach(slot => {
                const option = document.createElement('option');
                option.value = slot.time;
                option.textContent = slot.label;
                timeSelect.appendChild(option);
            });
        });
    </script>
</body>
</html>