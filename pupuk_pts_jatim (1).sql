-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 04, 2026 at 07:22 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pupuk_pts_jatim`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_settings`
--

CREATE TABLE `app_settings` (
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id` int(10) UNSIGNED NOT NULL,
  `pesanan_id` int(10) UNSIGNED NOT NULL,
  `produk_id` int(10) UNSIGNED NOT NULL,
  `nama_produk` varchar(150) NOT NULL COMMENT 'Snapshot nama saat transaksi',
  `satuan` enum('liter','kg') NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `harga_satuan` decimal(15,2) NOT NULL COMMENT 'Snapshot harga saat transaksi',
  `subtotal` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id`, `pesanan_id`, `produk_id`, `nama_produk`, `satuan`, `jumlah`, `harga_satuan`, `subtotal`) VALUES
(1, 1, 1, '', 'liter', 1.00, 5000.00, 5000.00),
(2, 2, 1, '', 'liter', 1.00, 5000.00, 5000.00),
(3, 3, 1, '', 'liter', 1.00, 5000.00, 5000.00),
(4, 4, 3, '', 'liter', 1.00, 100000.00, 100000.00),
(5, 5, 2, 'Insek', 'liter', 1.00, 6000.00, 6000.00),
(6, 5, 3, 'Kohe Kambing', 'kg', 3.00, 100000.00, 300000.00),
(7, 6, 6, 'Katalis Booster', 'liter', 1.00, 10000.00, 10000.00);

-- --------------------------------------------------------

--
-- Table structure for table `jenis_produk`
--

CREATE TABLE `jenis_produk` (
  `id` smallint(5) UNSIGNED NOT NULL,
  `kategori_id` tinyint(3) UNSIGNED NOT NULL,
  `nama_jenis` varchar(100) NOT NULL,
  `satuan` enum('liter','kg') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `jenis_produk`
--

INSERT INTO `jenis_produk` (`id`, `kategori_id`, `nama_jenis`, `satuan`) VALUES
(1, 1, 'Pembenah Tanah', 'liter'),
(2, 1, 'Urea Padat', 'kg'),
(3, 1, 'Urea Cair', 'liter'),
(4, 1, 'KCL', 'liter'),
(5, 1, 'ZPT Buah', 'liter'),
(6, 1, 'ZPT Daun', 'liter'),
(7, 1, 'Asam Amino', 'liter'),
(8, 1, 'Herbisida', 'liter'),
(9, 1, 'Insek', 'liter'),
(10, 1, 'Fungi', 'liter'),
(11, 1, 'Horti Padat', 'kg'),
(12, 1, 'Horti Cair', 'liter'),
(13, 1, 'Pengusir Tikus', 'liter'),
(14, 1, 'Asap Cair', 'liter'),
(15, 2, 'Kohe Sapi', 'kg'),
(16, 2, 'Kohe Kambing', 'kg'),
(17, 2, 'Kohe Ayam Pedaging', 'kg'),
(18, 2, 'Kohe Ayam Petelur', 'kg'),
(19, 2, 'Kencing Sapi', 'liter'),
(20, 2, 'Kencing Kambing', 'liter'),
(21, 2, 'Kencing Kelinci', 'liter'),
(22, 2, 'Air Kelapa', 'liter'),
(23, 2, 'Katalis Booster', 'liter'),
(24, 2, 'Kascing', 'kg'),
(25, 2, 'Rumen Sapi', 'kg'),
(26, 2, 'Gadung', 'kg'),
(27, 2, 'Sambiloto', 'kg');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_produk`
--

CREATE TABLE `kategori_produk` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `nama` varchar(50) NOT NULL COMMENT 'Pupuk / Bahan Baku'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kategori_produk`
--

INSERT INTO `kategori_produk` (`id`, `nama`) VALUES
(1, 'Pupuk'),
(2, 'Bahan Baku');

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL COMMENT 'Pembeli',
  `produk_id` int(10) UNSIGNED NOT NULL,
  `jumlah` decimal(10,2) NOT NULL DEFAULT 1.00,
  `added_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `keranjang`
--

INSERT INTO `keranjang` (`id`, `user_id`, `produk_id`, `jumlah`, `added_at`) VALUES
(4, 3, 1, 1.00, '2026-06-24 10:42:36'),
(8, 4, 3, 1.00, '2026-07-03 22:35:52');

-- --------------------------------------------------------

--
-- Table structure for table `konfirmasi_penjual`
--

CREATE TABLE `konfirmasi_penjual` (
  `id` int(10) UNSIGNED NOT NULL,
  `pesanan_id` int(10) UNSIGNED NOT NULL,
  `penjual_id` int(10) UNSIGNED NOT NULL,
  `aksi` enum('dikonfirmasi','ditolak') NOT NULL,
  `catatan` text DEFAULT NULL,
  `dilakukan_pada` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `konfirmasi_penjual`
--

INSERT INTO `konfirmasi_penjual` (`id`, `pesanan_id`, `penjual_id`, `aksi`, `catatan`, `dilakukan_pada`) VALUES
(1, 3, 2, 'dikonfirmasi', NULL, '2026-06-23 22:10:03'),
(2, 4, 2, 'dikonfirmasi', NULL, '2026-06-26 22:53:56'),
(3, 5, 2, 'dikonfirmasi', NULL, '2026-06-28 14:43:15'),
(4, 6, 5, 'dikonfirmasi', NULL, '2026-07-04 11:32:20');

-- --------------------------------------------------------

--
-- Table structure for table `log_admin`
--

CREATE TABLE `log_admin` (
  `id` int(10) UNSIGNED NOT NULL,
  `admin_id` int(10) UNSIGNED NOT NULL,
  `aksi` varchar(255) NOT NULL,
  `target_tabel` varchar(100) DEFAULT NULL,
  `target_id` int(10) UNSIGNED DEFAULT NULL,
  `keterangan` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi`
--

CREATE TABLE `notifikasi` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `judul` varchar(150) NOT NULL,
  `pesan` text NOT NULL,
  `tipe` enum('pesanan_masuk','konfirmasi','pengiriman','selesai','pembatalan','sistem') NOT NULL,
  `referensi_id` int(10) UNSIGNED DEFAULT NULL COMMENT 'id pesanan terkait',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_pesanan` varchar(30) NOT NULL COMMENT 'Format: ORD-YYYYMMDD-XXXXX',
  `pembeli_id` int(10) UNSIGNED NOT NULL,
  `toko_id` int(10) UNSIGNED NOT NULL,
  `total_harga` decimal(15,2) NOT NULL DEFAULT 0.00,
  `ongkir` decimal(15,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL DEFAULT 0.00,
  `alamat_kirim` text NOT NULL,
  `catatan` text DEFAULT NULL,
  `status` enum('menunggu_konfirmasi','dikonfirmasi','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'menunggu_konfirmasi',
  `metode_bayar` enum('transfer','cod','ewallet') NOT NULL DEFAULT 'transfer',
  `bukti_bayar` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bukti_pembayaran` varchar(255) DEFAULT NULL COMMENT 'File bukti pembayaran untuk admin review',
  `bukti_pengiriman` varchar(255) DEFAULT NULL COMMENT 'File bukti pengiriman dari penjual',
  `admin_approval_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending' COMMENT 'Status persetujuan admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id`, `kode_pesanan`, `pembeli_id`, `toko_id`, `total_harga`, `ongkir`, `grand_total`, `alamat_kirim`, `catatan`, `status`, `metode_bayar`, `bukti_bayar`, `created_at`, `updated_at`, `bukti_pembayaran`, `bukti_pengiriman`, `admin_approval_status`) VALUES
(1, 'ORD-20260623-3D50D', 3, 1, 5000.00, 0.00, 5000.00, '', NULL, 'selesai', 'transfer', NULL, '2026-06-23 21:16:54', '2026-06-30 18:22:06', NULL, NULL, 'pending'),
(2, 'ORD-20260623-17950', 3, 1, 5000.00, 0.00, 5000.00, '', NULL, 'selesai', 'transfer', NULL, '2026-06-23 21:48:35', '2026-06-30 18:20:24', NULL, NULL, 'approved'),
(3, 'ORD-20260623-CF6CA', 3, 1, 5000.00, 0.00, 5000.00, '', NULL, 'selesai', 'transfer', NULL, '2026-06-23 22:09:37', '2026-06-30 18:20:13', NULL, NULL, 'rejected'),
(4, 'ORD-20260626-F8B6A', 4, 1, 100000.00, 0.00, 100000.00, 'Mabna Rabi\'ah Al-Adawiyah Kampus 3 UIN Maulana Malik Ibrahim Malang, Jl. Locari, Krajan, Tlekung, Kec. Junrejo, Kabupaten Malang, Jawa Timur 65151', '', 'selesai', 'transfer', NULL, '2026-06-26 16:22:09', '2026-06-30 18:16:23', '1bb793cf45f1dd0e805143a2ea2235ee.png', NULL, 'pending'),
(5, 'ORD-20260628-02A59', 4, 1, 306000.00, 0.00, 306000.00, 'Mabna Rabi\'ah Al-Adawiyah Kampus 3 UIN Maulana Malik Ibrahim Malang, Jl. Locari, Krajan, Tlekung, Kec. Junrejo, Kabupaten Malang, Jawa Timur 65151', '', 'selesai', 'transfer', NULL, '2026-06-28 14:42:34', '2026-06-30 18:16:10', 'be9207492b66176f3e3bafa4dca647bf.png', NULL, 'approved'),
(6, 'ORD-20260704-5E4E6', 4, 2, 10000.00, 0.00, 10000.00, 'Mabna Rabi\'ah Al-Adawiyah Kampus 3 UIN Maulana Malik Ibrahim Malang, Jl. Locari, Krajan, Tlekung, Kec. Junrejo, Kabupaten Malang, Jawa Timur 65151', '', 'dikirim', 'transfer', NULL, '2026-07-04 11:32:00', '2026-07-04 11:32:34', '3c417ae5bd51bf98e31bc00804b8157c.png', NULL, 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id` int(10) UNSIGNED NOT NULL,
  `toko_id` int(10) UNSIGNED NOT NULL,
  `jenis_produk_id` smallint(5) UNSIGNED NOT NULL,
  `jumlah_stok` decimal(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Jumlah penjualan (liter/kg)',
  `harga_jual` decimal(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Harga per liter/kg, tidak termasuk ongkir',
  `deskripsi` text DEFAULT NULL,
  `foto_produk` varchar(255) DEFAULT NULL,
  `is_tersedia` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id`, `toko_id`, `jenis_produk_id`, `jumlah_stok`, `harga_jual`, `deskripsi`, `foto_produk`, `is_tersedia`, `created_at`, `updated_at`) VALUES
(1, 1, 22, 1.00, 5000.00, 'air kelapa enak', 'produk_1_1782224033.jpeg', 1, '2026-06-23 21:13:53', '2026-06-24 10:16:18'),
(2, 1, 9, 2.00, 6000.00, 'harum', 'produk_2_1782273440.png', 1, '2026-06-24 10:57:20', '2026-06-28 14:42:34'),
(3, 1, 16, 45.00, 100000.00, 'enak', 'produk_3_1782312880.png', 1, '2026-06-24 21:54:40', '2026-07-04 11:37:37'),
(6, 2, 23, 2999.00, 10000.00, 'bagus', 'produk_6_1783139211.png', 1, '2026-07-04 11:26:51', '2026-07-04 11:32:00'),
(7, 1, 18, 67.00, 55000.00, '', 'produk_7_1783140332.png', 1, '2026-07-04 11:45:32', '2026-07-04 11:45:32');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_produk`
--

CREATE TABLE `riwayat_produk` (
  `id` int(10) UNSIGNED NOT NULL,
  `produk_id` int(10) UNSIGNED NOT NULL,
  `jumlah_lama` decimal(12,2) NOT NULL,
  `jumlah_baru` decimal(12,2) NOT NULL,
  `harga_lama` decimal(15,2) NOT NULL,
  `harga_baru` decimal(15,2) NOT NULL,
  `diubah_pada` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `toko`
--

CREATE TABLE `toko` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `nama_toko` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `foto_toko` varchar(255) DEFAULT NULL,
  `bank_nama` varchar(100) DEFAULT NULL,
  `no_rekening` varchar(50) DEFAULT NULL,
  `nama_rekening` varchar(150) DEFAULT NULL,
  `total_terjual` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `toko`
--

INSERT INTO `toko` (`id`, `user_id`, `nama_toko`, `deskripsi`, `foto_toko`, `bank_nama`, `no_rekening`, `nama_rekening`, `total_terjual`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 2, 'jual najwa', 'sya menjual banyak', NULL, 'Bri', '24505858', 'Najwa Binti', 0, 1, '2026-06-23 21:12:29', '2026-07-03 20:44:34'),
(2, 5, 'Penjual nadia', 'halo aku n', NULL, 'BCA', '5673590', 'Nadia', 0, 1, '2026-07-04 11:26:00', '2026-07-04 11:29:56');

-- --------------------------------------------------------

--
-- Table structure for table `ulasan`
--

CREATE TABLE `ulasan` (
  `id` int(10) UNSIGNED NOT NULL,
  `pesanan_id` int(10) UNSIGNED NOT NULL,
  `produk_id` int(10) UNSIGNED NOT NULL,
  `pembeli_id` int(10) UNSIGNED NOT NULL,
  `rating` tinyint(3) UNSIGNED NOT NULL CHECK (`rating` between 1 and 5),
  `komentar` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama` varchar(150) NOT NULL,
  `angkatan` varchar(30) NOT NULL COMMENT 'Contoh: 2020, 2021, Alumni',
  `jenis_keanggotaan` enum('penjual','bukan_penjual') NOT NULL,
  `alamat` text NOT NULL,
  `no_telp` varchar(30) DEFAULT NULL COMMENT 'Nomor telepon pengguna',
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL COMMENT 'Bcrypt hash',
  `role` enum('admin','penjual','pembeli') NOT NULL DEFAULT 'pembeli',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `angkatan`, `jenis_keanggotaan`, `alamat`, `no_telp`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'Admin', 'bukan_penjual', 'Kantor PTS Jatim', NULL, 'admin@ptsjatim.id', '0192023a7bbd73250516f069df18b500', 'admin', 1, '2026-06-22 20:10:18', '2026-06-23 22:15:23'),
(2, 'Najwa Binti Alwi', '2020', 'penjual', 'Mabna Rabi\'ah Al-Adawiyah Kampus 3 UIN Maulana Malik Ibrahim Malang, Jl. Locari, Krajan, Tlekung, Kec. Junrejo, Kabupaten Malang, Jawa Timur 65151', NULL, 'najwaalwimak@gmail.com', '$2y$10$3N8V/8a2p9n1O1KpbJDkpOQ9c/5eQP5tWroXBJCddLxRfbN8UGqDK', 'penjual', 1, '2026-06-23 21:12:29', '2026-06-23 21:12:29'),
(3, 'nana', '2025', 'bukan_penjual', 'Mabna Rabi\'ah Al-Adawiyah Kampus 3 UIN Maulana Malik Ibrahim Malang, Jl. Locari, Krajan, Tlekung, Kec. Junrejo, Kabupaten Malang, Jawa Timur 65151', '085866789', 'nana@gmail.com', '$2y$10$v6OD1cVZQGhm89BkeIJ5L.yIW.Ou0HJQpyN2qxMXKfk9gZdRP/TVS', 'pembeli', 1, '2026-06-23 21:15:07', '2026-06-30 18:14:09'),
(4, 'frisya', '2023', 'bukan_penjual', 'Mabna Rabi\'ah Al-Adawiyah Kampus 3 UIN Maulana Malik Ibrahim Malang, Jl. Locari, Krajan, Tlekung, Kec. Junrejo, Kabupaten Malang, Jawa Timur 65151', '082241132782', 'nasia@gmail.com', '$2y$10$rcWEdwesjUHscAJMYlyWV.dqrLtmN8FClWtIJIrJL4dYiNqTF2Hry', 'pembeli', 1, '2026-06-26 16:20:54', '2026-06-26 16:20:54'),
(5, 'nadia', '2024', 'penjual', 'mbay ntt', '085928878793', 'atkcicanabati@gmail.com', '$2y$10$jEMqrhtNt5hhk.6bymOmi.JZcix17aJpe8xEVuKV2IlaMsNDNMAaK', 'penjual', 1, '2026-07-04 11:26:00', '2026-07-04 11:26:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `app_settings`
--
ALTER TABLE `app_settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_detail_pesanan` (`pesanan_id`),
  ADD KEY `fk_detail_produk` (`produk_id`);

--
-- Indexes for table `jenis_produk`
--
ALTER TABLE `jenis_produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jenis_kategori` (`kategori_id`);

--
-- Indexes for table `kategori_produk`
--
ALTER TABLE `kategori_produk`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_cart_item` (`user_id`,`produk_id`),
  ADD KEY `fk_keranjang_produk` (`produk_id`),
  ADD KEY `idx_keranjang_user` (`user_id`);

--
-- Indexes for table `konfirmasi_penjual`
--
ALTER TABLE `konfirmasi_penjual`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pesanan_id` (`pesanan_id`),
  ADD KEY `fk_konfirmasi_penjual` (`penjual_id`);

--
-- Indexes for table `log_admin`
--
ALTER TABLE `log_admin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_log_admin_id` (`admin_id`);

--
-- Indexes for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notifikasi_user` (`user_id`,`is_read`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_pesanan` (`kode_pesanan`),
  ADD KEY `idx_pesanan_pembeli` (`pembeli_id`),
  ADD KEY `idx_pesanan_toko` (`toko_id`),
  ADD KEY `idx_pesanan_status` (`status`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_toko_jenis` (`toko_id`,`jenis_produk_id`) COMMENT 'Satu toko hanya boleh punya 1 entri per jenis produk',
  ADD KEY `idx_produk_toko` (`toko_id`),
  ADD KEY `idx_produk_jenis` (`jenis_produk_id`);

--
-- Indexes for table `riwayat_produk`
--
ALTER TABLE `riwayat_produk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_riwayat_produk` (`produk_id`);

--
-- Indexes for table `toko`
--
ALTER TABLE `toko`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_ulasan` (`pesanan_id`,`produk_id`),
  ADD KEY `fk_ulasan_produk` (`produk_id`),
  ADD KEY `fk_ulasan_pembeli` (`pembeli_id`);

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
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `jenis_produk`
--
ALTER TABLE `jenis_produk`
  MODIFY `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `kategori_produk`
--
ALTER TABLE `kategori_produk`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `konfirmasi_penjual`
--
ALTER TABLE `konfirmasi_penjual`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `log_admin`
--
ALTER TABLE `log_admin`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi`
--
ALTER TABLE `notifikasi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `riwayat_produk`
--
ALTER TABLE `riwayat_produk`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `toko`
--
ALTER TABLE `toko`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ulasan`
--
ALTER TABLE `ulasan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detail_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);

--
-- Constraints for table `jenis_produk`
--
ALTER TABLE `jenis_produk`
  ADD CONSTRAINT `fk_jenis_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_produk` (`id`);

--
-- Constraints for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `fk_keranjang_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_keranjang_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `konfirmasi_penjual`
--
ALTER TABLE `konfirmasi_penjual`
  ADD CONSTRAINT `fk_konfirmasi_penjual` FOREIGN KEY (`penjual_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_konfirmasi_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `log_admin`
--
ALTER TABLE `log_admin`
  ADD CONSTRAINT `fk_log_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `notifikasi`
--
ALTER TABLE `notifikasi`
  ADD CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_pembeli` FOREIGN KEY (`pembeli_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_pesanan_toko` FOREIGN KEY (`toko_id`) REFERENCES `toko` (`id`);

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_jenis` FOREIGN KEY (`jenis_produk_id`) REFERENCES `jenis_produk` (`id`),
  ADD CONSTRAINT `fk_produk_toko` FOREIGN KEY (`toko_id`) REFERENCES `toko` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `riwayat_produk`
--
ALTER TABLE `riwayat_produk`
  ADD CONSTRAINT `fk_riwayat_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `toko`
--
ALTER TABLE `toko`
  ADD CONSTRAINT `fk_toko_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ulasan`
--
ALTER TABLE `ulasan`
  ADD CONSTRAINT `fk_ulasan_pembeli` FOREIGN KEY (`pembeli_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_ulasan_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ulasan_produk` FOREIGN KEY (`produk_id`) REFERENCES `produk` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
