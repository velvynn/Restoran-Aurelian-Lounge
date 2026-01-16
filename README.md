# 🍽️ RESTAURANT AURELIAN  
## Sistem Informasi Restoran Berbasis Web (PHP Native & MySQL)

Restaurant Aurelian adalah aplikasi web berbasis PHP Native dan MySQL yang dirancang untuk membantu proses operasional restoran secara digital. Sistem ini memungkinkan pelanggan melakukan pemesanan makanan, checkout, melihat riwayat pesanan, serta melakukan reservasi meja secara online. Di sisi lain, admin dapat mengelola data produk, kategori, pengguna, pesanan, reservasi, dan laporan penjualan melalui dashboard admin.

Aplikasi ini dikembangkan tanpa framework agar mudah dipahami oleh pemula dan cocok digunakan sebagai media pembelajaran, tugas perkuliahan, maupun portofolio.

---

## 📖 DAFTAR ISI
1. Latar Belakang  
2. Tujuan Pembuatan  
3. Ruang Lingkup Sistem  
4. Fitur Sistem  
5. Hak Akses Pengguna  
6. Struktur Folder Project  
7. Penjelasan File dan Folder  
8. Alur Kerja Sistem  
9. Perancangan Database (Konsep)  
10. Teknologi yang Digunakan  
11. Cara Instalasi dan Menjalankan Aplikasi  
12. Catatan Keamanan Sistem  
13. Rencana Pengembangan  
14. Author  
15. Penutup  

---

## 1. LATAR BELAKANG
Perkembangan teknologi informasi mendorong banyak sektor usaha untuk beralih ke sistem digital, termasuk di bidang kuliner dan restoran. Proses pemesanan manual sering menimbulkan antrean panjang, kesalahan pencatatan, serta kurang efisien dalam pengelolaan data. Oleh karena itu, diperlukan sebuah sistem informasi berbasis web yang mampu mengelola pemesanan dan reservasi secara terstruktur.

Restaurant Aurelian dikembangkan sebagai solusi sistem informasi restoran berbasis web yang dapat digunakan oleh pelanggan dan admin dalam satu sistem terintegrasi.

---

## 2. TUJUAN PEMBUATAN
Tujuan dari pembuatan aplikasi Restaurant Aurelian adalah:
- Membangun sistem informasi restoran berbasis web
- Menerapkan konsep CRUD (Create, Read, Update, Delete)
- Mengimplementasikan sistem login dan autentikasi
- Menerapkan pembagian hak akses (Admin dan Customer)
- Mempermudah pelanggan dalam melakukan pemesanan dan reservasi
- Mempermudah admin dalam mengelola data restoran
- Sebagai media pembelajaran dan tugas perkuliahan

---

## 3. RUANG LINGKUP SISTEM
Sistem ini mencakup:
- Pengelolaan data pengguna
- Pengelolaan data produk dan kategori
- Pemesanan makanan oleh customer
- Reservasi meja
- Pengelolaan pesanan oleh admin
- Pembuatan laporan penjualan

---

## 4. FITUR SISTEM

### 🔐 Autentikasi
- Registrasi pengguna
- Login pengguna
- Logout
- Manajemen session
- Redirect dashboard berdasarkan role

### 👤 Fitur Customer
- Melihat daftar menu makanan
- Menambahkan menu ke keranjang
- Menghapus dan mengubah jumlah pesanan di keranjang
- Checkout pesanan
- Melihat riwayat pesanan
- Melihat detail pesanan
- Melakukan reservasi meja
- Mengelola profil pengguna
- Upload foto profil
- Wishlist menu (opsional)

### 🛠️ Fitur Admin
- Dashboard admin
- Manajemen produk
- Manajemen kategori
- Manajemen pengguna
- Manajemen pesanan
- Manajemen reservasi
- Laporan penjualan

---

## 5. HAK AKSES PENGGUNA

### Admin
- Mengelola seluruh data sistem
- Mengelola produk dan kategori
- Mengelola pengguna
- Mengelola pesanan dan reservasi
- Melihat laporan penjualan

### Customer
- Melihat menu makanan
- Melakukan pemesanan
- Melakukan checkout
- Melihat riwayat pesanan
- Melakukan reservasi meja
- Mengelola profil akun

---

## 6. STRUKTUR FOLDER PROJECT
restaurant-aurelian/
├── index.php                    # Redirect utama
├── login.php                    # Halaman login
├── register.php                 # Halaman registrasi
├── logout.php                   # Logout process
├── dashboard.php                # Redirect berdasarkan role
├── includes/                    # Core system files
│   ├── config.php               # Konfigurasi database & site
│   ├── db_connect.php           # Database connection class
│   ├── auth.php                 # Authentication system
│   └── functions.php            # Helper functions
├── assets/                      # Static files
│   ├── css/
│   │   └── style.css            # Main stylesheet
│   ├── js/
│   │   └── script.js            # JavaScript functions
│   └── img/                     # Product images & icons
│       ├── img4.jpg
│       ├── img5.jpg
│       └── default-product.jpg
├── customer/                    # Customer dashboard
│   ├── index.php                # Customer homepage
│   ├── menu.php                 # Product catalog
│   ├── cart.php                # Shopping cart
│   ├── checkout.php            # Checkout process
│   ├── orders.php              # Order history
│   ├── order_details.php       # Order details
│   ├── reservation.php         # Table reservation
│   ├── profile.php             # User profile
│   └── wishlist.php            # Wishlist (optional)
├── admin/                      # Admin dashboard
│   ├── index.php              # Admin homepage
│   ├── products.php           # Product management
│   ├── orders.php             # Order management
│   ├── users.php              # User management
│   ├── categories.php         # Category management
│   ├── reservations.php       # Reservation management
│   └── reports.php            # Sales reports
└── uploads/                    # Uploaded files
    └── profile_photos/        # User profile photos

    
---

## 7. PENJELASAN FILE DAN FOLDER
- **index.php** : Halaman utama / redirect awal
- **login.php** : Halaman login pengguna
- **register.php** : Halaman registrasi pengguna
- **logout.php** : Proses logout
- **dashboard.php** : Redirect dashboard berdasarkan role

### Folder `includes/`
- **config.php** : Konfigurasi database dan website
- **db_connect.php** : Koneksi ke database MySQL
- **auth.php** : Sistem autentikasi dan session
- **functions.php** : Fungsi-fungsi pendukung

### Folder `assets/`
Berisi file CSS, JavaScript, dan gambar yang digunakan oleh sistem.

### Folder `customer/`
Berisi halaman khusus untuk customer.

### Folder `admin/`
Berisi halaman khusus untuk admin.

### Folder `uploads/`
Digunakan untuk menyimpan file upload seperti foto profil pengguna.

---

## 8. ALUR KERJA SISTEM
1. User mengakses website
2. User melakukan login atau registrasi
3. Sistem memverifikasi data login
4. User diarahkan ke dashboard sesuai role
5. Customer melakukan pemesanan atau reservasi
6. Admin mengelola data pesanan dan reservasi
7. Admin melihat laporan penjualan

---

## 9. PERANCANGAN DATABASE (KONSEP)
Tabel utama yang digunakan:
- users
- products
- categories
- orders
- order_details
- reservations
- wishlist

Relasi antar tabel menggunakan primary key dan foreign key.

---

## 10. TEKNOLOGI YANG DIGUNAKAN
- PHP Native
- MySQL
- HTML5
- CSS3
- JavaScript
- Apache Server (XAMPP / Laragon)

---

## 11. CARA INSTALASI DAN MENJALANKAN APLIKASI
1. Salin folder project ke dalam `htdocs`
2. Jalankan XAMPP atau Laragon
3. Import database ke MySQL
4. Atur konfigurasi database pada file `includes/config.php`
5. Akses aplikasi melalui browser:


---

## 12. CATATAN KEAMANAN SISTEM
- Gunakan hashing password
- Validasi input user
- Gunakan session untuk autentikasi
- Batasi akses halaman admin

---

## 13. RENCANA PENGEMBANGAN
- Integrasi payment gateway
- Notifikasi email
- Desain responsif
- Penggunaan framework Laravel
- REST API

---

## 14. AUTHOR

### Tim Pengembang
1. La Ode Kevin  
   Program Studi : Teknik Informatika  
   Universitas   : Universitas Surya Kancana  

2. Rani Maharani  
   Program Studi : Teknik Informatika  
   Universitas   : Universitas Surya Kancana  

3. Muggy Soewarman  
   Program Studi : Teknik Informatika  
   Universitas   : Universitas Surya Kancana  
  

---

## 15. PENUTUP
Aplikasi Restaurant Aurelian diharapkan dapat menjadi media pembelajaran dan solusi sederhana dalam pengelolaan restoran berbasis web. Sistem ini masih dapat dikembangkan lebih lanjut sesuai kebutuhan.
