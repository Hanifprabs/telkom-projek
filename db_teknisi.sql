-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 22, 2025 at 05:47 AM
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
  `user_id` int(11) DEFAULT NULL,
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

INSERT INTO `material_used` (`id`, `user_id`, `teknisi_id`, `wo`, `dc`, `s_calm`, `clam_hook`, `otp`, `prekso`, `soc_option`, `soc_value`, `precont_json`, `tiang`, `tanggal`, `precont_option`, `precont_value`, `dc_foto`, `deskripsi_masalah`, `status_masalah`) VALUES
(109, 5, 19, '2222', 0, 0, 0, 0, 0, '', 0, '{\"50\":\"\",\"75\":\"\",\"80\":\"\",\"100\":\"\",\"120\":\"\",\"135\":\"\",\"150\":\"\",\"180\":\"\"}', 0, '2025-10-21', 0, 0, 'uploads/1761018149_hologram.jpg', 'Cobaaaa', 'Sudah Dilihat'),
(112, 5, 10, '2222999999', 2, 3, 4, 5, 3, 'Sum', 3, '{\"50\":\"4\",\"75\":\"3\",\"80\":\"1\",\"100\":\"3\",\"120\":\"7\",\"135\":\"2\",\"150\":\"1\",\"180\":\"1\"}', 1, '2025-10-21', 0, 0, 'uploads/1761019974_hologram.jpg', 'Konslett', 'Belum Dilihat'),
(115, NULL, 10, '111111', 2, 4, 1, 2, 1, 'Sum', 12, '{\"50\":\"2\",\"75\":\"1\",\"80\":\"4\",\"100\":\"11\",\"120\":\"2\",\"135\":\"1\",\"150\":\"2\",\"180\":\"1\"}', 1, '0000-00-00', 0, 0, NULL, '', 'Belum Dilihat'),
(116, NULL, 9, '2222', 0, 0, 0, 0, 0, '', 0, '{\"50\":\"\",\"75\":\"\",\"80\":\"\",\"100\":\"\",\"120\":\"\",\"135\":\"\",\"150\":\"\",\"180\":\"\"}', 0, '0000-00-00', 0, 0, NULL, '', 'Belum Dilihat');

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
  `nik` int(11) DEFAULT NULL,
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
(4, 'RANTO', 15884832, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(6, ' DONI RACHMADI', 15894099, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(7, 'DIDIK HARYONO', 15894101, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(8, 'FARISAN ILHAM GUSWANTO', 16022153, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(9, 'ADI WIRAYUDHA', 16040574, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(10, 'SUBARNO', 16750151, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(11, 'TEGUH FAJAR BAWONO', 16770264, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(12, ' FAJAR YULIANTO', 16770346, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(13, ' RAHMAT SHOLEH', 16790433, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(14, 'ENDRO DWI JAHYANTO', 16860284, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(15, 'DODIK IRAWAN', 16880115, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(16, 'PRASETYO ARI WIBOWO', 16891146, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(17, 'CANDRA HARTANTO', 16900898, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(18, 'BUDI SETIAWAN', 16932282, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(19, 'AHMAD RANTO AFANDY', 16940046, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(20, 'RASYID ANDIKA PUTRA', 16995037, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(21, 'NURROSYID WANANDI PUTRA', 16995038, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N'),
(35, 'JAKA FEBRIANTO', 15893898, 'Kerten', 'PUTRA JAYA RAHARJA', '', '', 'N');

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
  `tiang` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `precont_option` int(11) DEFAULT NULL,
  `precont_value` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teknisi_detail`
--

INSERT INTO `teknisi_detail` (`id`, `teknisi_id`, `rfs`, `dc`, `s_calm`, `clam_hook`, `otp`, `prekso`, `soc_option`, `soc_value`, `precont_json`, `tiang`, `tanggal`, `precont_option`, `precont_value`) VALUES
(31, 10, '888yyuu', 12, 19, 14, 29, 24, 'Fuji', 9, '{\"50\":\"14\",\"75\":\"\",\"80\":\"18\",\"100\":\"\",\"120\":\"\",\"135\":\"18\",\"150\":\"\",\"180\":\"\"}', 20, '2025-10-21', NULL, NULL),
(35, 9, '2222', NULL, NULL, NULL, NULL, NULL, '', NULL, '{\"50\":\"\",\"75\":\"\",\"80\":\"\",\"100\":\"\",\"120\":\"\",\"135\":\"\",\"150\":\"\",\"180\":\"\"}', NULL, '2025-10-22', NULL, NULL),
(37, 7, '7777', NULL, NULL, NULL, NULL, NULL, '', NULL, '{\"50\":\"\",\"75\":\"\",\"80\":\"\",\"100\":\"\",\"120\":\"\",\"135\":\"\",\"150\":\"\",\"180\":\"\"}', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `telegram_id` bigint(20) DEFAULT NULL,
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

INSERT INTO `users` (`id`, `telegram_id`, `username`, `password`, `created_at`, `role`, `status`, `last_login`, `step`, `temp_data`) VALUES
(5, NULL, 'admin', '$2y$10$pawRJlYb.mYxm1oGEivdfuwNp6jiMNz8RkGGi3stDssoPL0JuGmQO', '2025-09-19 08:59:23', 'admin', 'active', NULL, NULL, NULL),
(14, 5820531737, 'RANTO', '$2y$10$ErmLl4d.eUieC7nHq59B1uWuc1A.F2ILlYK/pYC6YXUylj39dttCi', '2025-09-25 03:37:29', 'teknisi', 'inactive', '2025-10-06 10:35:40', NULL, NULL);

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
  ADD UNIQUE KEY `telegram_id` (`telegram_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `material_used`
--
ALTER TABLE `material_used`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `teknisi`
--
ALTER TABLE `teknisi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `teknisi_detail`
--
ALTER TABLE `teknisi_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `material_used`
--
ALTER TABLE `material_used`
  ADD CONSTRAINT `fk_user_material` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
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
