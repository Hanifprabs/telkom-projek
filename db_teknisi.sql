-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 10:02 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_teknisi`
--

-- --------------------------------------------------------

--
-- Table structure for table `material_used`
--

CREATE TABLE `material_used` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) DEFAULT NULL,
  `teknisi_id` int(11) NOT NULL,
  `wo` varchar(100) DEFAULT NULL,
  `dc` int(11) DEFAULT NULL,
  `s_calm` int(11) DEFAULT NULL,
  `clam_hook` int(11) DEFAULT NULL,
  `otp` int(11) DEFAULT NULL,
  `prekso` int(11) DEFAULT NULL,
  `soc_option` varchar(50) DEFAULT NULL,
  `soc_value` int(11) DEFAULT NULL,
  `precont_json` text DEFAULT NULL,
  `spliter_json` text DEFAULT NULL,
  `smoove_json` text DEFAULT NULL,
  `ad_sc` int(11) DEFAULT NULL,
  `tipe_pekerjaan` varchar(50) DEFAULT NULL,
  `tiang` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `precont_option` int(11) DEFAULT NULL,
  `precont_value` int(11) DEFAULT NULL,
  `dc_foto` text DEFAULT NULL,
  `deskripsi_masalah` text DEFAULT NULL,
  `status_masalah` enum('Belum Dilihat','Sudah Dilihat','Selesai') DEFAULT 'Belum Dilihat'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `material_used`
--

INSERT INTO `material_used` (`id`, `user_id`, `teknisi_id`, `wo`, `dc`, `s_calm`, `clam_hook`, `otp`, `prekso`, `soc_option`, `soc_value`, `precont_json`, `spliter_json`, `smoove_json`, `ad_sc`, `tipe_pekerjaan`, `tiang`, `tanggal`, `precont_option`, `precont_value`, `dc_foto`, `deskripsi_masalah`, `status_masalah`) VALUES
(126, 5, 20, '111111', 0, 0, 0, 0, 0, NULL, 0, '[]', '[]', '[]', 0, NULL, 0, '2025-11-07', NULL, NULL, 'uploads/1762506478_Hammer.png', 'Percobaan 2', 'Sudah Dilihat'),
(127, 5, 4, '22', 0, 0, 0, 0, 0, NULL, 0, '[]', '[]', '[]', 0, NULL, 0, '2025-11-11', NULL, NULL, 'uploads/1763451898_1000157809-removebg-preview.png', '', 'Belum Dilihat'),
(130, NULL, 4, '2', 2, 2, 2, 2, 2, 'sum', 2, '{\"50\":\"2\",\"75\":\"2\",\"80\":\"2\",\"100\":\"2\",\"120\":\"2\",\"135\":\"2\",\"150\":\"2\",\"180\":\"2\"}', '{\"1.2\":\"2\",\"1.4\":\"2\",\"1.8\":\"2\",\"1.16\":\"2\"}', '{\"Kecil\":\"2\",\"Tipe 3\":\"2\"}', 2, 'Maintenance', 2, '2025-11-11', 0, 0, 'uploads/file_5.jpg', 'Percobaan', 'Selesai'),
(132, NULL, 4, '22333', 2, 4, 4, 2, 4, 'fuji', 8, '{\"50\":\"4\",\"75\":\"4\",\"80\":\"2\",\"100\":\"2\",\"120\":\"2\",\"135\":\"0\",\"150\":\"0\",\"180\":\"0\"}', '{\"1.2\":\"0\",\"1.4\":\"0\",\"1.8\":\"7\",\"1.16\":\"0\"}', '{\"Kecil\":\"0\",\"Tipe 3\":\"1\"}', 2, 'Mitratel', 4, '2025-11-12', 0, 0, 'uploads/file_7.jpg', 'Percobaan berikutnya', 'Sudah Dilihat'),
(133, 5, 9, '2222', 0, 0, 0, 0, 0, NULL, 0, '[]', '[]', '[]', 0, NULL, 0, '2025-11-17', 0, 0, 'uploads/file_8.jpg', 'Percobaan 22', 'Belum Dilihat'),
(135, NULL, 9, '2', 3, 2, 1, 1, 0, 'sum', 0, '{\"50\":\"4\",\"75\":\"4\",\"80\":\"1\",\"100\":\"1\",\"120\":\"1\",\"135\":\"1\",\"150\":\"1\",\"180\":\"1\"}', '{\"1.2\":\"1\",\"1.4\":\"1\",\"1.8\":\"1\",\"1.16\":\"1\"}', '{\"Kecil\":\"1\",\"Tipe 3\":\"1\"}', 0, 'Mitratel', 2, '2025-11-17', 0, 0, '', '0', 'Belum Dilihat'),
(137, NULL, 4, '222', 2, 3, 1, 2, 1, 'sum', 2, '{\"50\":\"2\",\"75\":\"2\",\"80\":\"2\",\"100\":\"2\",\"120\":\"0\",\"135\":\"2\",\"150\":\"0\",\"180\":\"0\"}', '{\"1.2\":\"0\",\"1.4\":\"0\",\"1.8\":\"0\",\"1.16\":\"0\"}', '{\"Kecil\":\"2\",\"Tipe 3\":\"1\"}', 2, 'Maintenance', 0, '2025-11-17', 0, 0, 'uploads/file_9.jpg', 'konslet', 'Selesai'),
(140, 5, 6, '2', 0, 0, 0, 0, 0, NULL, 0, '[]', '[]', '[]', 0, NULL, 0, '2025-11-18', 0, 0, 'uploads/1763453791_Logo_FST.png', '222', 'Belum Dilihat'),
(141, 85, 4, '2', 2, 2, 3, 1, 1, 'fuji', 2, '{\"50\":\"0\",\"75\":\"0\",\"80\":\"0\",\"100\":\"0\",\"120\":\"1\",\"135\":\"2\",\"150\":\"0\",\"180\":\"1\"}', '{\"1.2\":\"1\",\"1.4\":\"1\",\"1.8\":\"1\",\"1.16\":\"1\"}', '{\"Kecil\":\"1\",\"Tipe 3\":\"1\"}', 1, 'Maintenance', 1, '2025-11-18', 0, 0, 'uploads/file_12.jpg', 'mencobaa', 'Belum Dilihat'),
(143, 5, 6, '222222', 2, 2, 2, 2, 1, '', 2, '{\"50\":\"2\",\"75\":\"2\",\"80\":\"2\",\"100\":\"2\",\"120\":\"2\",\"135\":\"2\",\"150\":\"2\",\"180\":\"2\"}', '{\"1.2\":\"2\",\"1.4\":\"2\",\"1.8\":\"2\",\"1.16\":\"2\"}', '{\"Kecil\":\"2\",\"Tipe 3\":\"2\"}', 2, 'Provisioning', 1, '2025-11-18', 0, 0, '', NULL, 'Belum Dilihat');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `chat_id` bigint(20) NOT NULL,
  `step` varchar(50) DEFAULT NULL,
  `teknisi_id` int(11) DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`chat_id`, `step`, `teknisi_id`, `data`) VALUES
(5820531737, 'precont_180', 4, '{\"wo\":\"-\",\"dc\":\"-\",\"s_calm\":\"12\",\"clam_hook\":\"13\",\"otp\":\"44\",\"prekso\":\"5\",\"soc_option\":\"2\",\"soc_value\":\"1\",\"tiang\":\"2\",\"tanggal\":\"2025-09-26\",\"precont_75\":\"20\",\"precont_80\":\"24\",\"precont_100\":\"3\",\"precont_120\":\"1\",\"precont_135\":\"2\",\"precont_150\":\"3\"}');

-- --------------------------------------------------------

--
-- Table structure for table `teknisi`
--

CREATE TABLE `teknisi` (
  `id` int(11) NOT NULL,
  `namatek` varchar(255) NOT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `sektor` varchar(255) NOT NULL,
  `mitra` varchar(255) NOT NULL,
  `idtele` varchar(255) DEFAULT NULL,
  `crew` varchar(255) NOT NULL,
  `valid` char(5) NOT NULL DEFAULT 'n'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teknisi`
--

INSERT INTO `teknisi` (`id`, `namatek`, `nik`, `sektor`, `mitra`, `idtele`, `crew`, `valid`) VALUES
(4, 'RANTO', '15884832', 'Kerten', 'PUTRA JAYA RAHARJA', '8581326564', '', 'N'),
(6, ' DONI RACHMADI', '15894099', 'Kerten', 'PUTRA JAYA RAHARJA', '5820531737', '', 'N'),
(7, 'DIDIK HARYONO', '15894101', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(8, 'FARISAN ILHAM GUSWANTO', '16022153', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(9, 'ADI WIRAYUDHA', '16040574', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(10, 'SUBARNO', '16750151', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(11, 'TEGUH FAJAR BAWONO', '16770264', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(12, ' FAJAR YULIANTO', '16770346', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(13, ' RAHMAT SHOLEH', '16790433', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(14, 'ENDRO DWI JAHYANTO', '16860284', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(15, 'DODIK IRAWAN', '16880115', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(16, 'PRASETYO ARI WIBOWO', '16891146', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(17, 'CANDRA HARTANTO', '16900898', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(18, 'BUDI SETIAWAN', '16932282', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(19, 'AHMAD RANTO AFANDY', '16940046', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(20, 'RASYID ANDIKA PUTRA', '16995037', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(21, 'NURROSYID WANANDI PUTRA', '16995038', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(35, 'JAKA FEBRIANTO', '15893898', 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N');

-- --------------------------------------------------------

--
-- Table structure for table `teknisi_detail`
--

CREATE TABLE `teknisi_detail` (
  `id` int(11) NOT NULL,
  `teknisi_id` int(11) NOT NULL,
  `rfs` varchar(100) DEFAULT NULL,
  `dc` int(11) DEFAULT NULL,
  `s_calm` int(11) DEFAULT NULL,
  `clam_hook` int(11) DEFAULT NULL,
  `otp` int(11) DEFAULT NULL,
  `prekso` int(11) DEFAULT NULL,
  `soc_option` varchar(50) DEFAULT NULL,
  `soc_value` int(11) DEFAULT NULL,
  `precont_json` text DEFAULT NULL,
  `spliter_json` text DEFAULT NULL,
  `smoove_json` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`smoove_json`)),
  `tiang` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `precont_option` int(11) DEFAULT NULL,
  `precont_value` int(11) DEFAULT NULL,
  `ad_sc` int(11) DEFAULT NULL,
  `tipe_pekerjaan` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teknisi_detail`
--

INSERT INTO `teknisi_detail` (`id`, `teknisi_id`, `rfs`, `dc`, `s_calm`, `clam_hook`, `otp`, `prekso`, `soc_option`, `soc_value`, `precont_json`, `spliter_json`, `smoove_json`, `tiang`, `tanggal`, `precont_option`, `precont_value`, `ad_sc`, `tipe_pekerjaan`) VALUES
(56, 20, '2222', 3, 3, 3, 3, 3, 'Fuji', 3, '{\"50\":\"3\",\"75\":\"3\",\"80\":\"3\",\"100\":\"3\",\"120\":\"3\",\"135\":\"3\",\"150\":\"3\",\"180\":\"3\"}', '{\"1.2\":\"3\",\"1.4\":\"3\",\"1.8\":\"3\",\"1.16\":\"3\"}', '{\"Kecil\":\"3\",\"Tipe 3\":\"3\"}', 3, '2025-11-07', NULL, NULL, 3, 'IOAN'),
(58, 4, '222', 4, 3, 2, 2, 2, 'Fuji', 2, '{\"50\":\"4\",\"75\":\"4\",\"80\":\"2\",\"100\":\"2\",\"120\":\"2\",\"135\":\"2\",\"150\":\"2\",\"180\":\"2\"}', '{\"1.2\":\"2\",\"1.4\":\"2\",\"1.8\":\"2\",\"1.16\":\"2\"}', '{\"Kecil\":\"2\",\"Tipe 3\":\"2\"}', 2, '2025-11-14', NULL, NULL, 2, 'Maintenance');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) NOT NULL,
  `telegram_id` bigint(20) DEFAULT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('admin','teknisi') NOT NULL DEFAULT 'teknisi',
  `status` enum('active','inactive') DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `step` varchar(50) DEFAULT NULL,
  `temp_data` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `telegram_id`, `nik`, `username`, `password`, `created_at`, `role`, `status`, `last_login`, `step`, `temp_data`) VALUES
(5, NULL, NULL, 'admin', '$2y$10$pawRJlYb.mYxm1oGEivdfuwNp6jiMNz8RkGGi3stDssoPL0JuGmQO', '2025-09-19 08:59:23', 'admin', 'active', NULL, NULL, NULL),
(37, 5820531737, '15894099', 'DONI', '$2y$10$PWJhZh.lAGlDFNI8CoyhSu34RC8ZiiR7fvTTnky3pZ.0eA3OJOLmq', '2025-11-17 01:53:09', 'teknisi', 'active', '2025-11-18 15:52:03', NULL, NULL),
(85, 8581326564, '15884832', 'RANTO', '$2y$10$kMTXwb.oJjO4oTJlfD0MSOESLnaVpmhsM/6nijsaxBNAaNCKfkFGu', '2025-11-17 08:54:21', 'teknisi', 'inactive', '2025-11-18 15:14:08', 'login', NULL),
(91, NULL, '16932282', 'BUDI', '$2y$10$22o5HHUxblfLkhacr0utNOPxL1c7HA4MVV6YJqkwgaJHZpksphXjq', '2025-11-18 06:44:01', 'teknisi', 'active', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `material_used`
--
ALTER TABLE `material_used`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teknisi_id` (`teknisi_id`),
  ADD KEY `fk_user_material` (`user_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`chat_id`);

--
-- Indexes for table `teknisi`
--
ALTER TABLE `teknisi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `teknisi_detail`
--
ALTER TABLE `teknisi_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teknisi_id` (`teknisi_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `unique_username` (`username`),
  ADD UNIQUE KEY `telegram_id` (`telegram_id`),
  ADD UNIQUE KEY `telegram_id_2` (`telegram_id`),
  ADD UNIQUE KEY `telegram_id_3` (`telegram_id`),
  ADD UNIQUE KEY `unique_nik` (`nik`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `material_used`
--
ALTER TABLE `material_used`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `teknisi`
--
ALTER TABLE `teknisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `teknisi_detail`
--
ALTER TABLE `teknisi_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `material_used`
--
ALTER TABLE `material_used`
  ADD CONSTRAINT `fk_user_material` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `material_used_ibfk_1` FOREIGN KEY (`teknisi_id`) REFERENCES `teknisi` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `teknisi_detail`
--
ALTER TABLE `teknisi_detail`
  ADD CONSTRAINT `teknisi_detail_ibfk_1` FOREIGN KEY (`teknisi_id`) REFERENCES `teknisi` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
