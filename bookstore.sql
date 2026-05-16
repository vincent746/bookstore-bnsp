-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 16, 2026 at 04:30 AM
-- Server version: 8.0.30
-- PHP Version: 8.3.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookstore`
--

-- --------------------------------------------------------

--
-- Table structure for table `m_buku`
--

CREATE TABLE `m_buku` (
  `id` int NOT NULL,
  `id_kategori` int NOT NULL,
  `judul_buku` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `penulis` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `penerbit` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tahun_penerbit` date NOT NULL,
  `harga` decimal(10,2) NOT NULL DEFAULT '0.00',
  `stok` int DEFAULT '0',
  `gambar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_active` tinyint DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `m_buku`
--

INSERT INTO `m_buku` (`id`, `id_kategori`, `judul_buku`, `deskripsi`, `penulis`, `penerbit`, `tahun_penerbit`, `harga`, `stok`, `gambar`, `is_active`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(3, 10, 'Belajar Laravel untuk Pemula', 'ini adalah buku laravel untuk pelajaran pemula', 'vincent', 'Event Media', '2026-05-16', '200000.00', 4, '1778903601_images.jpg', 1, '2026-05-16 09:55:36', 1, '2026-05-16 10:53:21', 1),
(4, 11, 'Senja di Ujung Kota', 'ini buku ini adalah senja di ujung kota', 'Rizky Ramadhan', 'Pustaka Nusantara', '2026-04-08', '89000.00', 10, '1778903614_shopping.jpg', 1, '2026-05-16 10:43:05', 1, '2026-05-16 10:53:34', 1),
(5, 12, 'Strategi Bisnis Digital', 'ini adalah buku untuk strategi bisnis digital jika anda ingin mengembangkan bisnis digital', 'Andi Saputra', 'Digital Press', '2026-03-05', '150000.00', 10, '1778903662_download__2_.jpg', 1, '2026-05-16 10:44:20', 1, '2026-05-16 10:54:22', 1);

-- --------------------------------------------------------

--
-- Table structure for table `m_contact`
--

CREATE TABLE `m_contact` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `deskripsi` text,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `m_contact`
--

INSERT INTO `m_contact` (`id`, `nama`, `email`, `deskripsi`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'Vincent', 'putravincent746@gmail.com', 'hai', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `m_kategori`
--

CREATE TABLE `m_kategori` (
  `id` int NOT NULL,
  `initial` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `nama_kategori` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` tinyint DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `m_kategori`
--

INSERT INTO `m_kategori` (`id`, `initial`, `nama_kategori`, `is_active`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(10, 'TKN', 'Teknologi', 1, '2026-05-16 09:54:16', 1, NULL, NULL),
(11, 'NVL', 'Novel', 1, '2026-05-16 10:41:22', 1, NULL, NULL),
(12, 'BSN', 'Bisnis', 1, '2026-05-16 10:41:35', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `m_status`
--

CREATE TABLE `m_status` (
  `id` int NOT NULL,
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `is_active` int NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `m_status`
--

INSERT INTO `m_status` (`id`, `status`, `is_active`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'Menunggu Pembayaran', 1, '2026-05-14 19:19:57', 1, NULL, NULL),
(2, 'Menunggu Konfirmasi', 1, '2026-05-14 19:20:18', 1, NULL, NULL),
(3, 'Terkonfirmasi', 1, '2026-05-14 19:20:31', 1, NULL, NULL),
(4, 'Dibatalkan', 1, '2026-05-14 19:20:42', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `m_status_pengiriman`
--

CREATE TABLE `m_status_pengiriman` (
  `id` int NOT NULL,
  `status_pengiriman` varchar(100) NOT NULL,
  `is_active` int DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `m_status_pengiriman`
--

INSERT INTO `m_status_pengiriman` (`id`, `status_pengiriman`, `is_active`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(1, 'Diproses', 1, '2026-05-16 09:43:06', 1, NULL, NULL),
(2, 'Dikemas', 1, '2026-05-16 09:43:14', 1, NULL, NULL),
(3, 'Dikirim', 1, '2026-05-16 09:43:23', 1, NULL, NULL),
(4, 'Sampai Tujuan', 1, '2026-05-16 09:43:35', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trans_d_pesanan`
--

CREATE TABLE `trans_d_pesanan` (
  `id` int NOT NULL,
  `id_header` int NOT NULL,
  `id_buku` int NOT NULL,
  `harga_satuan` decimal(10,2) NOT NULL,
  `jumlah_buku` int NOT NULL DEFAULT '1',
  `total_harga` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trans_d_pesanan`
--

INSERT INTO `trans_d_pesanan` (`id`, `id_header`, `id_buku`, `harga_satuan`, `jumlah_buku`, `total_harga`) VALUES
(7, 6, 4, '89000.00', 1, '89000.00'),
(8, 6, 5, '150000.00', 1, '150000.00'),
(9, 7, 3, '200000.00', 1, '200000.00'),
(10, 7, 4, '89000.00', 1, '89000.00');

-- --------------------------------------------------------

--
-- Table structure for table `trans_h_pesanan`
--

CREATE TABLE `trans_h_pesanan` (
  `id` int NOT NULL,
  `kode_transaksi` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_user` int NOT NULL,
  `bukti_pembayaran` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `deskripsi_pengiriman` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `is_paid` tinyint DEFAULT '0',
  `id_status` int DEFAULT NULL,
  `id_pengirim` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `updated_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `trans_h_pesanan`
--

INSERT INTO `trans_h_pesanan` (`id`, `kode_transaksi`, `id_user`, `bukti_pembayaran`, `deskripsi`, `deskripsi_pengiriman`, `is_paid`, `id_status`, `id_pengirim`, `created_at`, `created_by`, `updated_at`, `updated_by`) VALUES
(6, 'BK-20260516-8399', 2, '1778903101_Gemini_Generated_Image_z1ze54z1ze54z1ze.png', 'test', '', 0, 4, 4, '2026-05-16 10:44:41', NULL, '2026-05-16 10:55:07', 1),
(7, 'BK-20260516-9788', 2, '1778903766_Gemini_Generated_Image_z1ze54z1ze54z1ze.png', 'batal', NULL, 0, 4, NULL, '2026-05-16 10:55:55', NULL, '2026-05-16 10:56:37', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','user') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'user',
  `is_active` tinyint DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `created_by` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `is_active`, `created_at`, `created_by`) VALUES
(1, 'Admin', 'admin@gmail.com', '$2y$10$lA4IcI4SBGgx3yBTC2DE4eC8O7RDXVLKY9/hr6m1YwQrs1rt6hTWy', 'admin', 1, '2026-05-14 15:27:56', NULL),
(2, 'Vincent', 'putravincent746@gmail.com', '$2y$10$lA4IcI4SBGgx3yBTC2DE4eC8O7RDXVLKY9/hr6m1YwQrs1rt6hTWy', 'user', 1, '2026-05-15 18:55:34', NULL),
(3, 'Risyaldi', 'risyaldi123@gmail.com', '$2y$10$AAXiyEF68fTWNZqO8Pjz4.2S45rpMwVNLWQ8mqbzwVMl/nWjD1i4a', 'user', 1, '2026-05-15 14:07:18', 3);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `m_buku`
--
ALTER TABLE `m_buku`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `m_contact`
--
ALTER TABLE `m_contact`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `m_kategori`
--
ALTER TABLE `m_kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `m_status`
--
ALTER TABLE `m_status`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `m_status_pengiriman`
--
ALTER TABLE `m_status_pengiriman`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `trans_d_pesanan`
--
ALTER TABLE `trans_d_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_header` (`id_header`),
  ADD KEY `id_buku` (`id_buku`);

--
-- Indexes for table `trans_h_pesanan`
--
ALTER TABLE `trans_h_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_transaksi` (`kode_transaksi`),
  ADD KEY `id_user` (`id_user`);

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
-- AUTO_INCREMENT for table `m_buku`
--
ALTER TABLE `m_buku`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `m_contact`
--
ALTER TABLE `m_contact`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `m_kategori`
--
ALTER TABLE `m_kategori`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `m_status`
--
ALTER TABLE `m_status`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `m_status_pengiriman`
--
ALTER TABLE `m_status_pengiriman`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `trans_d_pesanan`
--
ALTER TABLE `trans_d_pesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `trans_h_pesanan`
--
ALTER TABLE `trans_h_pesanan`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `m_buku`
--
ALTER TABLE `m_buku`
  ADD CONSTRAINT `m_buku_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `m_kategori` (`id`);

--
-- Constraints for table `trans_d_pesanan`
--
ALTER TABLE `trans_d_pesanan`
  ADD CONSTRAINT `trans_d_pesanan_ibfk_1` FOREIGN KEY (`id_header`) REFERENCES `trans_h_pesanan` (`id`),
  ADD CONSTRAINT `trans_d_pesanan_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `m_buku` (`id`);

--
-- Constraints for table `trans_h_pesanan`
--
ALTER TABLE `trans_h_pesanan`
  ADD CONSTRAINT `trans_h_pesanan_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
