-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Waktu pembuatan: 16 Jan 2026 pada 09.45
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `restaurant_aurelian`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_value` text DEFAULT NULL,
  `new_value` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'LOGIN', NULL, NULL, NULL, 'User admin logged in', '127.0.0.1', 'Mozilla/5.0', '2024-01-15 09:00:00'),
(2, 1, 'CREATE', 'products', 25, NULL, 'Product Pudding Caramel created', '127.0.0.1', 'Mozilla/5.0', '2024-01-16 10:30:00'),
(3, 1, 'UPDATE', 'orders', 2, 'status=pending', 'status=processing', '127.0.0.1', 'Mozilla/5.0', '2024-01-16 14:30:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `product_id`, `quantity`, `notes`, `created_at`) VALUES
(1, 5, 1, 2, 'Pedas banget', '2024-02-03 10:00:00'),
(2, 5, 13, 2, 'Kurang manis', '2024-02-03 10:00:00'),
(3, 6, 7, 1, 'Daging empuk', '2024-02-03 11:30:00'),
(4, 1, 6, 1, NULL, '2026-01-14 03:48:33'),
(5, 5, 20, 2, NULL, '2026-01-16 06:35:52');

-- --------------------------------------------------------

--
-- Struktur dari tabel `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `image`, `created_at`, `updated_at`) VALUES
(1, 'Breakfast', 'Menu sarapan pagi yang sehat dan bergizi', 'breakfast.jpg', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(2, 'Lunch', 'Menu makan siang spesial', 'lunch.jpg', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(3, 'Dinner', 'Menu makan malam romantis', 'dinner.jpg', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(4, 'Drink', 'Minuman segar dan sehat', 'drink.jpg', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(5, 'Appetizer', 'Hidangan pembuka', 'appetizer.jpg', '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(6, 'Dessert', 'Makanan penutup manis', 'dessert.jpg', '2024-01-01 00:00:00', '2024-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_code` varchar(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','completed','cancelled') DEFAULT 'pending',
  `payment_method` enum('cash','bank_transfer','credit_card','e_wallet') DEFAULT 'cash',
  `payment_status` enum('pending','paid','failed') DEFAULT 'pending',
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_address` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `orders`
--

INSERT INTO `orders` (`id`, `order_code`, `user_id`, `total_amount`, `status`, `payment_method`, `payment_status`, `customer_name`, `customer_phone`, `customer_address`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'ORD-202401-001', 5, 85000.00, 'completed', 'cash', 'paid', 'John Doe', '081111111111', 'Jl. Customer No. 1', 'Tambah sambal', '2024-01-15 10:30:00', '2024-01-15 11:30:00'),
(2, 'ORD-202401-002', 5, 120000.00, 'processing', 'bank_transfer', 'paid', 'John Doe', '081111111111', 'Jl. Customer No. 1', 'Packaging yang rapi', '2024-01-16 14:20:00', '2024-01-16 14:30:00'),
(3, 'ORD-202401-003', 6, 65000.00, 'pending', 'e_wallet', 'pending', 'Jane Smith', '082222222222', 'Jl. Customer No. 2', 'Tanpa cabe', '2024-01-17 09:15:00', '2024-01-17 09:15:00'),
(4, 'ORD-202402-001', 5, 95000.00, 'completed', 'credit_card', 'paid', 'John Doe', '081111111111', 'Jl. Customer No. 1', NULL, '2024-02-01 12:45:00', '2024-02-01 13:30:00'),
(5, 'ORD-202402-002', 7, 75000.00, 'cancelled', 'cash', 'failed', 'Robert Johnson', '083333333333', 'Jl. Customer No. 3', 'Batal pesanan', '2024-02-02 16:10:00', '2024-02-02 16:30:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `notes`) VALUES
(1, 1, 1, 'Mie Carbonara', 2, 20000.00, 'Pedas'),
(2, 1, 13, 'Fresh Lemon Tea', 1, 13000.00, 'Kurang manis'),
(3, 1, 17, 'Orange Juice', 2, 10000.00, 'Tanpa es'),
(4, 2, 7, 'Rendang', 1, 100000.00, 'Daging empuk'),
(5, 2, 19, 'Juice Alpukat', 1, 15000.00, NULL),
(6, 2, 14, 'Iced Coffee Milk', 1, 15000.00, 'Extra sugar'),
(7, 3, 3, 'Spagety Vegetable', 1, 20000.00, 'Vegetarian'),
(8, 3, 2, 'Pokcoy Lada Hitam', 2, 10000.00, 'Tidak pedas'),
(9, 3, 13, 'Fresh Lemon Tea', 1, 13000.00, 'Tanpa gula'),
(10, 4, 5, 'Tempe Mendoan', 2, 40000.00, 'Crispy'),
(11, 4, 13, 'Fresh Lemon Tea', 1, 13000.00, NULL),
(12, 4, 24, 'Brownies Cokelat', 1, 35000.00, 'Untuk ulang tahun'),
(13, 5, 1, 'Mie Carbonara', 1, 20000.00, NULL),
(14, 5, 14, 'Iced Coffee Milk', 2, 15000.00, NULL),
(15, 5, 18, 'Juice Semangka', 2, 12000.00, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','success','failed') DEFAULT 'pending',
  `payment_proof` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `amount`, `transaction_id`, `status`, `payment_proof`, `notes`, `created_at`, `updated_at`) VALUES
(1, 1, 'cash', 85000.00, 'CASH-001', 'success', NULL, 'Bayar tunai', '2024-01-15 10:35:00', '2024-01-15 10:35:00'),
(2, 2, 'bank_transfer', 120000.00, 'BRI-20240116001', 'success', 'proof_bri_001.jpg', 'Transfer BRI', '2024-01-16 14:25:00', '2024-01-16 14:25:00'),
(3, 3, 'e_wallet', 65000.00, NULL, 'pending', NULL, 'Menunggu pembayaran OVO', '2024-01-17 09:20:00', '2024-01-17 09:20:00'),
(4, 4, 'credit_card', 95000.00, 'VISA-20240201001', 'success', 'proof_visa_001.jpg', 'Kartu kredit Visa', '2024-02-01 12:50:00', '2024-02-01 12:50:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 100,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_featured` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `original_price`, `category_id`, `image`, `stock`, `is_active`, `is_featured`, `created_at`, `updated_at`) VALUES
(1, 'Mie Carbonara', 'Mie cremy dengan keju lumer dan cabe bubuk, bikin lidah bergoyang', 20000.00, 25000.00, 2, 'img4.jpg', 50, 1, 1, '2024-01-01 00:00:00', '2026-01-16 06:32:57'),
(2, 'Pokcoy Lada Hitam', 'Pokcoy dengan saus lada hitam spesial', 10000.00, 12000.00, 2, 'img5.jpg', 30, 1, 1, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(3, 'Spagety Vegetable', 'Spageti sayuran dengan cita rasa nusantara', 20000.00, 30000.00, 2, 'img6.jpg', 40, 1, 1, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(4, 'Telor Gulung Cabe', 'Telur gulung dengan rasa pedas, digulung dengan rasa cinta', 45000.00, 50000.00, 2, 'img7.jpg', 20, 1, 0, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(5, 'Tempe Mendoan', 'Tempe mendoan crispy dengan bumbu khas, crispy di luar juicy di dalam', 40000.00, 50000.00, 3, 'img8.jpg', 25, 1, 1, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(6, 'Basreng Balado', 'Basreng pedas balado, pedasnya sepedas mulut tetangga', 25000.00, 30000.00, 3, 'img9.jpg', 35, 1, 0, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(7, 'Rendang', 'Rendang daging sapi dengan bumbu rempah pilihan', 100000.00, NULL, 3, 'img10.jpg', 15, 1, 1, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(8, 'Roti Telur Saus', 'Roti dengan telur dan saus spesial, dipanggang dengan niat', 20000.00, NULL, 1, 'img11.jpg', 40, 1, 0, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(9, 'Ubi Unggu Keju', 'Ubi ungu dengan taburan keju khusus', 10000.00, NULL, 3, 'img12.jpg', 30, 1, 0, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(10, 'Stup Roti', 'Roti dengan sensasi creamy yang nagih', 100000.00, NULL, 1, 'img13.jpg', 10, 1, 0, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(11, 'Mie Kuah Kari Tomat', 'Mie dengan kuah kari tomat segar dan topping tomat', 40000.00, NULL, 2, 'img14.jpg', 20, 1, 0, '2024-01-01 00:00:00', '2024-01-01 00:00:00'),
(12, 'Sandwich Premium', 'Sandwich dengan roti premium dan isian spesial', 150000.00, NULL, 1, 'img35.webp', 25, 1, 1, '2024-01-01 00:00:00', '2026-01-14 03:48:10'),
(13, 'Fresh Lemon Tea', 'Teh lemon segar dengan lemon pilihan', 13000.00, NULL, 4, 'img24.webp', 100, 1, 1, '2024-01-01 00:00:00', '2026-01-14 03:42:06'),
(14, 'Iced Coffee Milk', 'Kopi susu dingin yang menyegarkan, serasa melek seharian', 15000.00, NULL, 4, 'img25.jpg', 80, 1, 1, '2024-01-01 00:00:00', '2026-01-14 03:41:21'),
(15, 'Strawberry Smoothie', 'Smoothie stroberi segar, semanis hubunganku dengan dia', 12000.00, NULL, 4, 'img36.webp', 60, 1, 0, '2024-01-01 00:00:00', '2026-01-14 03:40:25'),
(16, 'Chocolate Milkshake', 'Milkshake cokelat yang creamy, secandu genggamanmu', 17000.00, NULL, 4, 'img23.webp', 70, 1, 0, '2024-01-01 00:00:00', '2026-01-14 03:47:00'),
(17, 'Orange Juice', 'Jus jeruk segar, jeruknya dipetik langsung dari depan rumah', 10000.00, NULL, 4, 'img32.webp', 90, 1, 0, '2024-01-01 00:00:00', '2026-01-14 03:46:25'),
(18, 'Juice Semangka', 'Jus semangka menyegarkan, semangkanya seger banget', 12000.00, NULL, 4, 'img29.webp', 75, 1, 0, '2024-01-01 00:00:00', '2026-01-14 03:45:51'),
(19, 'Juice Alpukat', 'Jus alpukat lembut, sentuhan manis bikin mood naik', 15000.00, NULL, 4, 'img26.webp', 50, 1, 1, '2024-01-01 00:00:00', '2026-01-14 03:45:29'),
(20, 'Juice Buah Naga', 'Jus buah naga merah, pink meriah rasa segar', 10000.00, NULL, 4, 'img28.webp', 40, 1, 0, '2024-01-01 00:00:00', '2026-01-14 03:44:59'),
(21, 'Juice Sirsak', 'Jus sirsak segar, asam-manis kayak hubungan yang nggak jelas', 15000.00, NULL, 4, 'img30.webp', 45, 1, 0, '2024-01-01 00:00:00', '2026-01-14 03:44:33'),
(22, 'Salad Buah Segar', 'Salad buah dengan yogurt dan madu', 25000.00, 30000.00, 5, 'img34.webp', 30, 1, 1, '2024-01-01 00:00:00', '2026-01-14 03:47:47'),
(23, 'Kentang Goreng', 'Kentang goreng crispy dengan saus spesial', 18000.00, NULL, 5, 'img31.jpeg', 50, 1, 0, '2024-01-01 00:00:00', '2026-01-14 03:43:45'),
(24, 'Brownies Cokelat', 'Brownies cokelat lembut dengan topping almond', 35000.00, NULL, 6, 'img22.jpg', 25, 1, 1, '2024-01-01 00:00:00', '2026-01-14 03:43:13'),
(25, 'Pudding Caramel', 'Pudding caramel dengan saus vanilla', 28000.00, NULL, 6, 'img33.webp', 40, 1, 0, '2024-01-01 00:00:00', '2026-01-14 03:42:57');

-- --------------------------------------------------------

--
-- Struktur dari tabel `promotions`
--

CREATE TABLE `promotions` (
  `id` int(11) NOT NULL,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `discount_type` enum('percentage','fixed') DEFAULT 'percentage',
  `discount_value` decimal(10,2) NOT NULL,
  `min_order` decimal(10,2) DEFAULT 0.00,
  `max_discount` decimal(10,2) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `usage_limit` int(11) DEFAULT NULL,
  `used_count` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `promotions`
--

INSERT INTO `promotions` (`id`, `code`, `name`, `description`, `discount_type`, `discount_value`, `min_order`, `max_discount`, `start_date`, `end_date`, `usage_limit`, `used_count`, `is_active`, `created_at`) VALUES
(1, 'WELCOME10', 'Welcome Discount', 'Diskon 10% untuk pelanggan baru', 'percentage', 10.00, 50000.00, 20000.00, '2024-01-01', '2024-12-31', 1000, 25, 1, '2024-01-01 00:00:00'),
(2, 'AURELIAN20', 'Special Discount', 'Diskon 20% minimal pembelian 100k', 'percentage', 20.00, 100000.00, 50000.00, '2024-01-01', '2024-12-31', 500, 12, 1, '2024-01-01 00:00:00'),
(3, 'CASHBACK15', 'Cashback Special', 'Cashback Rp 15.000 untuk semua transaksi', 'fixed', 15000.00, 75000.00, 15000.00, '2024-02-01', '2024-02-29', 200, 8, 1, '2024-02-01 00:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_email` varchar(100) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `reservation_date` date NOT NULL,
  `reservation_time` time NOT NULL,
  `number_of_people` int(11) NOT NULL,
  `table_type` varchar(20) DEFAULT 'regular',
  `special_request` text DEFAULT NULL,
  `status` enum('pending','confirmed','cancelled','completed') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `reservation_date`, `reservation_time`, `number_of_people`, `table_type`, `special_request`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 5, 'John Doe', 'customer1@aurelian.com', '081111111111', '2024-02-15', '19:00:00', 4, 'regular', 'Meja dekat jendela', 'confirmed', 'Ulang tahun', '2024-02-01 10:00:00', '2024-02-01 10:30:00'),
(2, 6, 'Jane Smith', 'customer2@aurelian.com', '082222222222', '2024-02-20', '18:30:00', 2, 'regular', 'Vegetarian menu', 'pending', NULL, '2024-02-02 11:00:00', '2024-02-02 11:00:00'),
(3, NULL, 'Michael Brown', 'michael@email.com', '083333333333', '2024-02-25', '20:00:00', 6, 'vip', 'Acara keluarga', 'pending', NULL, '2024-02-03 09:30:00', '2024-02-03 09:30:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `rating` int(1) NOT NULL,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ;

--
-- Dumping data untuk tabel `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `order_id`, `rating`, `comment`, `created_at`) VALUES
(1, 5, 1, 1, 5, 'Enak banget! Kejunya lumer dan mie nya pas', '2024-01-15 12:00:00'),
(2, 5, 13, 1, 4, 'Lemon teanya segar, tidak terlalu manis', '2024-01-15 12:00:00'),
(3, 5, 7, 2, 5, 'Rendangnya empuk banget, bumbunya meresap', '2024-01-16 15:00:00'),
(4, 6, 2, 3, 4, 'Pokcoynya segar, saus lada hitamnya mantap', '2024-01-17 10:00:00'),
(5, 6, 3, 3, 3, 'Spagetinya enak, tapi kurang pedas', '2024-01-17 10:00:00'),
(6, 5, 5, 4, 5, 'Tempe mendoannya crispy dan gurih', '2024-02-01 14:00:00'),
(7, 5, 24, 4, 5, 'Browniesnya lembut dan coklatnya nyata', '2024-02-01 14:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `site_settings`
--

CREATE TABLE `site_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text NOT NULL,
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `site_settings`
--

INSERT INTO `site_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'site_name', 'Aurelian Lounge', 'Nama website/restoran', '2024-01-01 00:00:00'),
(2, 'site_email', 'info@aurelianlounge.com', 'Email utama', '2024-01-01 00:00:00'),
(3, 'site_phone', '+62 822 9665 0035', 'Nomor telepon', '2024-01-01 00:00:00'),
(4, 'site_address', 'Perumahan Arwinda Asri, Cianjur', 'Alamat restoran', '2024-01-01 00:00:00'),
(5, 'opening_hours', '{\"weekday\": \"09:00-21:00\", \"weekend\": \"10:00-20:00\"}', 'Jam operasional', '2024-01-01 00:00:00'),
(6, 'currency', 'IDR', 'Mata uang', '2024-01-01 00:00:00'),
(7, 'tax_percentage', '10', 'Persentase pajak', '2024-01-01 00:00:00'),
(8, 'delivery_fee', '15000', 'Biaya pengiriman', '2024-01-01 00:00:00'),
(9, 'min_order_amount', '50000', 'Minimum order', '2024-01-01 00:00:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','user','manager','chef','waiter','cashier','inventory') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `address`, `role`, `created_at`, `updated_at`, `last_login`) VALUES
(1, 'admin', 'admin@aurelian.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '081234567890', 'Jl. Administrasi No. 1', 'admin', '2024-01-01 00:00:00', '2026-01-16 06:32:27', '2026-01-16 06:32:27'),
(2, 'kevin', 'kevin@aurelian.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'La Ode Kevin', '082299660035', 'Perumahan Arwinda Asri, Cianjur', 'admin', '2024-01-01 00:00:00', '2024-01-01 00:00:00', NULL),
(3, 'rani', 'rani@aurelian.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rani Maharani', '082321535902', 'Jakarta Selatan', 'admin', '2024-01-01 00:00:00', '2024-01-01 00:00:00', NULL),
(4, 'mugy', 'mugy@aurelian.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Muggy Soewarman', '087714644192', 'Bandung', 'admin', '2024-01-01 00:00:00', '2024-01-01 00:00:00', NULL),
(5, 'customer1', 'customer1@aurelian.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '081111111111', 'Jl. Customer No. 1', 'user', '2024-01-01 00:00:00', '2026-01-16 06:35:30', '2026-01-16 06:35:30'),
(6, 'customer2', 'customer2@aurelian.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', '082222222222', 'Jl. Customer No. 2', 'user', '2024-01-01 00:00:00', '2024-01-01 00:00:00', NULL),
(7, 'customer3', 'customer3@aurelian.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert Johnson', '083333333333', 'Jl. Customer No. 3', 'user', '2024-01-01 00:00:00', '2024-01-01 00:00:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_code` (`order_code`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`);

--
-- Indeks untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeks untuk tabel `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `idx_featured` (`is_featured`);

--
-- Indeks untuk tabel `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indeks untuk tabel `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indeks untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indeks untuk tabel `site_settings`
--
ALTER TABLE `site_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT untuk tabel `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `site_settings`
--
ALTER TABLE `site_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Ketidakleluasaan untuk tabel `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
