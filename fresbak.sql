-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 07, 2025 at 09:37 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fresbak`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_address` text NOT NULL,
  `customer_contact` varchar(20) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `payment_proof` varchar(255) DEFAULT NULL,
  `status` enum('pending','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'pending',
  `order_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `customer_name`, `customer_address`, `customer_contact`, `total_price`, `payment_method`, `payment_proof`, `status`, `order_date`) VALUES
(12, 3, 'Ridho Nur Maulana', 'Jogja', '082139822731', 8375000.00, 'Bank Transfer - Mandiri', 'proof_12_693542cd487e8.png', 'selesai', '2025-12-07 09:01:25');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `product_id` int NOT NULL,
  `quantity` int NOT NULL,
  `price_at_order` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price_at_order`) VALUES
(10, 12, 20, 1, 3500000.00),
(11, 12, 21, 1, 1950000.00),
(12, 12, 22, 1, 2200000.00),
(13, 12, 27, 2, 350000.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` varchar(50) DEFAULT 'Umum',
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `stock` int NOT NULL DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `description`, `price`, `stock`, `image`, `created_at`, `is_active`) VALUES
(16, 'Meja Makan Jati \"Nusantara\" (4 Kursi)', 'Meja', 'Set meja makan dari kayu jati asli dengan finishing natural walnut. Tahan air dan anti rayap. Ukuran pas untuk keluarga kecil (120x70 cm).', 2800000.00, 3, '1765096772_69353d4440637.jpg', '2025-12-07 08:39:32', 1),
(18, 'Kursi Cafe Rotan \"Bali Vibes\"', 'Kursi', 'Kursi santai perpaduan kayu sungkai dan anyaman rotan alami pada sandaran. Memberikan nuansa tropis dan estetik pada ruangan atau teras rumah.', 450000.00, 12, '1765097019_69353e3ba9507.jpg', '2025-12-07 08:43:39', 1),
(20, 'Sofa L Minimalis \"Scandi\" Abu-abu', 'Sofa', 'Sofa sudut bentuk L dengan desain Skandinavian. Bahan kain canvas lembut, busa empuk tidak mudah kempes, dan kaki kayu solid yang kokoh. Cocok untuk ruang keluarga modern.', 3500000.00, 4, '1765097146_69353eba1a61e.jpg', '2025-12-07 08:45:46', 1),
(21, 'Lemari Pakaian 2 Pintu \"White Gloss\"', 'Lemari', 'Lemari pakaian modern dengan finishing putih mengkilap (high gloss). Dilengkapi cermin full body di salah satu pintu dan laci penyimpanan di dalam.', 1950000.00, 3, '1765097294_69353f4ed61a5.jpg', '2025-12-07 08:48:14', 1),
(22, 'Dipan Kayu Queen \"Zen Style\"', 'Tempat Tidur', 'Rangka tempat tidur model rendah (low profile) ala Jepang. Terbuat dari kayu mahoni solid. Tanpa headboard untuk tampilan kamar yang lebih luas dan bersih.', 2200000.00, 1, '1765097450_69353fead11d3.jpg', '2025-12-07 08:50:50', 1),
(23, 'Rak Besi Industrial \"The Loft\"', 'Rak', 'Rak serbaguna dengan rangka besi hollow hitam dan ambalan kayu motif serat kasar. Sangat kuat untuk menaruh buku berat atau pot tanaman hias.', 850000.00, 8, '1765097546_6935404a3f318.jpg', '2025-12-07 08:52:26', 1),
(24, 'Standing Lamp \"Arco\" Gold', 'Lampu', 'Lampu lantai dengan tiang melengkung warna emas yang elegan. Kap lampu berbentuk bola kaca memberikan cahaya menyebar yang hangat (warm white).', 650000.00, 10, '1765097634_693540a205122.jpg', '2025-12-07 08:53:54', 1),
(25, 'Cermin Dinding \"Sunburst\" Rotan', 'Dekorasi', 'Cermin hias gantung dengan bingkai rotan berbentuk matahari. Diameter total 60cm. Menambah kesan artistik dan boho pada dinding polos.', 250000.00, 15, '1765097717_693540f56cb02.jpg', '2025-12-07 08:55:17', 1),
(26, 'Meja Kerja Lipat Dinding \"Compact\"', 'Meja', 'Solusi untuk kamar sempit. Meja kerja yang ditempel di dinding dan bisa dilipat saat tidak digunakan. Material Plywood HPL tahan gores.', 550000.00, 7, '1765097863_69354187ad9ab.jpg', '2025-12-07 08:57:43', 1),
(27, 'Bean Bag Triangle \"Cozy\" (Termasuk Isi)', 'Sofa', 'Kursi santai tanpa rangka yang mengikuti bentuk tubuh. Cover bahan katun motif grid estetik, bisa dilepas dan dicuci. Sudah termasuk isi butiran styrofoam.', 350000.00, 18, '1765097926_693541c690fa4.jpg', '2025-12-07 08:58:46', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(3, 'Ridho', 'ridhonur@gmail.com', '$2y$10$Nz9C2UqPo8Ly717uPq1KqOufgLaTjvG9AlzlMpMwEBMz57IRw8nE2', 'admin', '2025-11-11 16:12:45'),
(6, 'Ridho Nur Maulana', 'ridho@gmail.com', '$2y$10$EdlGN8xf01QYfL7ml.tigehNPnx3.fITUv5rTSMz2WomX9ueVqPNO', 'customer', '2025-12-07 08:28:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
