-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 08:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.5.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dtr_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `todtr`
--

CREATE TABLE `todtr` (
  `id` int(11) NOT NULL,
  `employee_no` varchar(50) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `travel_type` enum('Authority to Travel','Locator Slip','Pass Slip') NOT NULL,
  `date_filed` date NOT NULL,
  `departure_date` date NOT NULL,
  `arrival_date` date NOT NULL,
  `departure_time` time DEFAULT NULL,
  `arrival_time` time DEFAULT NULL,
  `source_table` varchar(50) NOT NULL,
  `source_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `todtr`
--

INSERT INTO `todtr` (`id`, `employee_no`, `full_name`, `travel_type`, `date_filed`, `departure_date`, `arrival_date`, `departure_time`, `arrival_time`, `source_table`, `source_id`, `created_at`, `updated_at`) VALUES
(65, '', 'Redgine Pinedes', 'Authority to Travel', '2026-01-22', '2026-01-22', '2026-01-22', '00:00:00', '00:00:00', 'authority_to_travel', 1, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(66, '', 'Redgine Pinedes', 'Authority to Travel', '2026-01-22', '2026-01-22', '2026-01-22', '00:00:00', '00:00:00', 'authority_to_travel', 2, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(67, '', 'Redgine Pinedes', 'Authority to Travel', '2026-01-22', '2026-01-22', '2026-01-22', '00:00:00', '00:00:00', 'authority_to_travel', 3, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(68, '', 'Redgine Pinedes', 'Authority to Travel', '2026-01-22', '2026-01-22', '2026-01-25', '00:00:00', '00:00:00', 'authority_to_travel', 4, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(69, '', 'Redgine Pinedes', 'Authority to Travel', '2026-01-22', '2026-01-22', '2026-01-22', '00:00:00', '00:00:00', 'authority_to_travel', 5, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(70, '', 'Redgine Pinedes', 'Authority to Travel', '2026-01-22', '2026-01-22', '2026-01-22', '00:00:00', '00:00:00', 'authority_to_travel', 6, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(71, '', 'Redgine Pinedes', 'Authority to Travel', '2026-01-22', '2026-01-22', '2026-01-29', '00:00:00', '00:00:00', 'authority_to_travel', 8, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(72, '', 'Redgine Pinedes', 'Authority to Travel', '2026-01-22', '2026-01-22', '2026-01-23', '00:00:00', '00:00:00', 'authority_to_travel', 9, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(73, '108435140100', 'Lyka Jane Leosala', 'Authority to Travel', '2026-01-22', '2026-01-22', '2026-01-23', '00:00:00', '00:00:00', 'authority_to_travel', 10, '2026-02-20 15:13:05', '2026-03-04 05:59:07'),
(74, '', 'Algen Loveres', 'Authority to Travel', '2026-01-22', '2026-01-22', '2026-01-28', '00:00:00', '00:00:00', 'authority_to_travel', 11, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(75, '', 'Redgine Pinedes', 'Authority to Travel', '2026-01-23', '2026-01-23', '2026-01-31', '00:00:00', '00:00:00', 'authority_to_travel', 12, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(76, '', 'Cedrick Bacaresas', 'Authority to Travel', '2026-01-23', '2026-01-23', '2026-01-24', '00:00:00', '00:00:00', 'authority_to_travel', 14, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(77, '', 'John Daniel P. Tec', 'Authority to Travel', '2026-01-23', '2026-01-23', '2026-01-24', '00:00:00', '00:00:00', 'authority_to_travel', 15, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(78, '122632', 'Eljohn S. Beleta', 'Authority to Travel', '2026-01-26', '2026-01-26', '2026-01-27', '00:00:00', '00:00:00', 'authority_to_travel', 21, '2026-02-20 15:13:05', '2026-03-04 05:59:07'),
(79, '', 'Redgine Pinedes', 'Authority to Travel', '2026-02-04', '2026-02-04', '2026-02-08', '00:00:00', '00:00:00', 'authority_to_travel', 24, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(80, '', 'Jennesh Larena', 'Authority to Travel', '2026-02-05', '2026-02-05', '2026-02-07', '00:00:00', '00:00:00', 'authority_to_travel', 25, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(81, '', 'Jennesh Larena', 'Authority to Travel', '2026-02-05', '2026-02-07', '2026-02-09', '00:00:00', '00:00:00', 'authority_to_travel', 26, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(82, '1233665', 'Emelinda Amil', 'Authority to Travel', '2026-02-05', '2026-02-05', '2026-02-08', '00:00:00', '00:00:00', 'authority_to_travel', 27, '2026-02-20 15:13:05', '2026-03-04 05:59:07'),
(83, '', 'Redgine Pinedes', 'Authority to Travel', '2026-02-05', '2026-02-05', '2026-02-12', '00:00:00', '00:00:00', 'authority_to_travel', 28, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(84, '', 'Paul Jeremy I. Aguja', 'Authority to Travel', '2026-02-05', '2026-02-05', '2026-02-28', '00:00:00', '00:00:00', 'authority_to_travel', 29, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(85, '108435140100', 'Lyka Jane Leosala', 'Authority to Travel', '2026-02-13', '2026-02-13', '2026-02-14', '00:00:00', '00:00:00', 'authority_to_travel', 30, '2026-02-20 15:13:05', '2026-03-04 05:59:07'),
(86, '', 'Phillip B. Gallendez', 'Authority to Travel', '2026-02-17', '2026-02-17', '2026-02-18', '00:00:00', '00:00:00', 'authority_to_travel', 31, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(87, '', 'Joe-Bren L. Consuelo', 'Authority to Travel', '2026-02-17', '2026-02-17', '2026-02-20', '00:00:00', '00:00:00', 'authority_to_travel', 33, '2026-02-20 15:13:05', '2026-02-20 15:26:26'),
(88, '1233665', 'Emelinda Amil', 'Authority to Travel', '2026-02-18', '2026-02-18', '2026-02-23', '00:00:00', '00:00:00', 'authority_to_travel', 36, '2026-02-20 15:13:05', '2026-03-04 05:59:07'),
(89, '1233665', 'Emelinda Amil', 'Authority to Travel', '2026-02-18', '2026-02-18', '2026-02-23', '00:00:00', '00:00:00', 'authority_to_travel', 41, '2026-02-20 15:13:05', '2026-03-04 05:59:07'),
(90, '1233665', 'Emelinda Amil', 'Authority to Travel', '2026-02-18', '2026-02-20', '2026-02-22', '00:00:00', '00:00:00', 'authority_to_travel', 42, '2026-02-20 15:13:05', '2026-03-04 05:59:07'),
(91, '1233665', 'Emelinda Amil', 'Authority to Travel', '2026-02-18', '2026-02-18', '2026-02-21', '00:00:00', '00:00:00', 'authority_to_travel', 44, '2026-02-20 15:13:05', '2026-03-04 05:59:07'),
(92, '1233665', 'Emelinda Amil', 'Authority to Travel', '2026-02-18', '2026-02-18', '2026-02-24', '00:00:00', '00:00:00', 'authority_to_travel', 45, '2026-02-20 15:13:05', '2026-03-04 05:59:07'),
(93, '108435140100', 'Lyka Jane Leosala', 'Authority to Travel', '2026-02-19', '2026-02-21', '2026-02-22', '00:00:00', '00:00:00', 'authority_to_travel', 46, '2026-02-20 15:13:05', '2026-03-04 05:59:07'),
(94, '108435140100', 'Lyka Jane Leosala', 'Authority to Travel', '2026-02-19', '2026-02-28', '2026-03-19', '00:00:00', '00:00:00', 'authority_to_travel', 47, '2026-02-20 15:13:05', '2026-03-04 05:59:07'),
(96, '', 'Redgine Pinedes', 'Locator Slip', '2026-01-21', '2026-01-21', '2026-01-21', '15:29:00', '15:29:00', 'locator_slips', 1, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(97, '', 'Redgine Pinedes', 'Locator Slip', '2026-01-21', '2026-01-21', '2026-01-21', '15:29:00', '15:29:00', 'locator_slips', 2, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(98, '', 'Redgine Pinedes', 'Locator Slip', '2026-01-21', '2026-01-21', '2026-01-21', '15:29:00', '15:29:00', 'locator_slips', 3, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(99, '', 'Redgine Pinedes', 'Locator Slip', '2026-01-21', '2026-01-21', '2026-01-21', '16:41:00', '16:41:00', 'locator_slips', 4, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(100, '', 'Redgine Pinedes', 'Locator Slip', '2026-01-21', '2026-01-21', '2026-01-21', '16:44:00', '16:44:00', 'locator_slips', 5, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(101, '', 'Redgine Pinedes', 'Locator Slip', '2026-01-21', '2026-01-21', '2026-01-21', '16:48:00', '16:48:00', 'locator_slips', 6, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(102, '', 'Redgine Pinedes', 'Locator Slip', '2026-01-21', '2026-01-21', '2026-01-21', '16:56:00', '16:56:00', 'locator_slips', 7, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(103, '', 'Redgine Pinedes', 'Locator Slip', '2026-01-21', '2026-01-21', '2026-01-21', '16:56:00', '16:56:00', 'locator_slips', 8, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(104, '', 'Algen Loveres', 'Locator Slip', '2026-01-22', '2026-01-22', '2026-01-22', '10:04:00', '10:04:00', 'locator_slips', 10, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(105, '', 'Algen Loveres', 'Locator Slip', '2026-01-22', '2026-01-22', '2026-01-22', '10:00:00', '10:00:00', 'locator_slips', 11, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(106, '', 'Redgine Pinedes', 'Locator Slip', '2026-01-23', '2026-01-23', '2026-01-23', '03:44:00', '03:44:00', 'locator_slips', 12, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(107, '', 'Jenina Ambayec', 'Locator Slip', '2026-01-30', '2026-01-30', '2026-01-30', '22:00:00', '22:00:00', 'locator_slips', 14, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(108, '', 'Jennesh Larena', 'Locator Slip', '2026-02-05', '2026-02-06', '2026-02-06', '17:00:00', '17:00:00', 'locator_slips', 18, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(109, '', 'Jennesh Larena', 'Locator Slip', '2026-02-05', '2026-02-09', '2026-02-09', '09:12:00', '09:12:00', 'locator_slips', 20, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(110, '1233665', 'Emelinda Amil', 'Locator Slip', '2026-02-05', '2026-02-05', '2026-02-05', '00:00:00', '00:00:00', 'locator_slips', 21, '2026-02-20 15:33:20', '2026-03-04 05:59:07'),
(111, '', 'Paul Jeremy I. Aguja', 'Locator Slip', '2026-02-05', '2026-02-05', '2026-02-05', '20:50:00', '20:50:00', 'locator_slips', 22, '2026-02-20 15:33:20', '2026-02-20 15:33:20'),
(112, '108435140100', 'Lyka Jane Leosala', 'Locator Slip', '2026-02-19', '2026-02-19', '2026-02-19', '00:00:00', '00:00:00', 'locator_slips', 28, '2026-02-20 15:33:20', '2026-03-04 05:59:07'),
(127, '108435140100', 'Lyka Jane Leosala', 'Pass Slip', '2026-02-12', '2026-02-12', '2026-02-12', '10:00:00', '13:00:00', 'pass_slips', 1, '2026-02-20 15:33:20', '2026-03-04 05:59:07'),
(128, '', 'Paul Jeremy I. Aguja', 'Pass Slip', '2026-02-12', '2026-02-12', '2026-02-12', '01:45:00', '17:43:00', 'pass_slips', 2, '2026-02-20 15:33:20', '2026-02-24 06:15:29'),
(129, '1233665', 'Emelinda Amil', 'Pass Slip', '2026-02-12', '2026-02-12', '2026-02-12', '10:20:00', '13:26:00', 'pass_slips', 3, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(130, '', 'Erma S. Valenzuela', 'Pass Slip', '2026-02-12', '2026-02-12', '2026-02-12', NULL, NULL, 'pass_slips', 4, '2026-02-20 15:33:20', '2026-02-24 06:15:29'),
(131, '108435140100', 'Lyka Jane Leosala', 'Pass Slip', '2026-02-15', '2026-02-15', '2026-02-15', '08:00:00', '12:00:00', 'pass_slips', 5, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(132, '108435140100', 'Lyka Jane Leosala', 'Pass Slip', '2026-02-20', '2026-02-20', '2026-02-20', '09:00:00', '12:00:00', 'pass_slips', 6, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(133, '108435140100', 'Lyka Jane Leosala', 'Pass Slip', '2026-02-21', '2026-02-21', '2026-02-21', '09:00:00', '12:00:00', 'pass_slips', 7, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(134, '108435140100', 'Lyka Jane Leosala', 'Pass Slip', '2026-02-20', '2026-02-20', '2026-02-20', '16:30:00', '17:30:00', 'pass_slips', 8, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(135, '108435140100', 'Lyka Jane Leosala', 'Pass Slip', '2026-02-20', '2026-02-20', '2026-02-20', '16:30:00', '17:30:00', 'pass_slips', 9, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(136, '108435140100', 'Lyka Jane Leosala', 'Pass Slip', '2026-02-20', '2026-02-20', '2026-02-20', '16:30:00', '17:30:00', 'pass_slips', 13, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(137, '108435140100', 'Lyka Jane Leosala', 'Pass Slip', '2026-02-21', '2026-02-21', '2026-02-21', '08:00:00', '11:00:00', 'pass_slips', 14, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(138, '108435140100', 'Lyka Jane Leosala', 'Pass Slip', '2026-02-20', '2026-02-20', '2026-02-20', '16:21:00', '19:21:00', 'pass_slips', 15, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(139, '122632', 'Eljohn S. Beleta', 'Pass Slip', '2026-02-21', '2026-02-21', '2026-02-21', '10:00:00', '13:00:00', 'pass_slips', 16, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(140, '122632', 'Eljohn S. Beleta', 'Pass Slip', '2026-02-21', '2026-02-21', '2026-02-21', '14:00:00', '17:00:00', 'pass_slips', 17, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(141, '122632', 'Eljohn S. Beleta', 'Pass Slip', '2026-02-22', '2026-02-22', '2026-02-22', '09:00:00', '12:00:00', 'pass_slips', 18, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(142, '122632', 'Eljohn S. Beleta', 'Pass Slip', '2026-02-22', '2026-02-22', '2026-02-22', '06:00:00', '09:00:00', 'pass_slips', 19, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(143, '1233665', 'Emelinda Amil', 'Pass Slip', '2026-02-21', '2026-02-21', '2026-02-21', '09:00:00', '12:00:00', 'pass_slips', 20, '2026-02-20 15:33:20', '2026-03-04 05:59:06'),
(158, '1233665', 'Emelinda Amil', 'Pass Slip', '2026-02-21', '2026-02-21', '2026-02-21', '12:00:00', '15:00:00', 'pass_slips', 21, '2026-02-20 15:35:52', '2026-03-04 05:59:06'),
(159, '', 'Ana Marie Mercado', 'Pass Slip', '2026-02-20', '2026-02-21', '2026-02-21', '23:41:30', '23:41:37', 'pass_slips', 22, '2026-02-20 15:40:22', '2026-02-24 06:15:29'),
(160, '1233665', 'Emelinda Amil', 'Pass Slip', '2026-02-24', '2026-02-24', '2026-02-24', '14:00:00', '17:00:00', 'pass_slips', 23, '2026-02-24 05:47:18', '2026-03-04 05:59:06'),
(162, '108435140100', 'Lyka Jane Leosala', 'Pass Slip', '2026-02-24', '2026-02-24', '2026-02-24', '15:00:00', '18:00:00', 'pass_slips', 24, '2026-02-24 06:00:30', '2026-03-04 05:59:06'),
(163, '122632', 'Eljohn S. Beleta', 'Pass Slip', '2026-02-24', '2026-02-24', '2026-02-24', '14:30:00', '17:30:00', 'pass_slips', 25, '2026-02-24 06:11:37', '2026-03-04 05:59:06'),
(171, '', 'Ana Marie Mercado', 'Pass Slip', '2026-02-24', '2026-02-24', '2026-02-24', '15:02:28', '15:09:51', 'pass_slips', 26, '2026-02-24 07:02:08', '2026-02-24 07:09:51'),
(304, '108435140100', 'Lyka Jane Leosala', 'Authority to Travel', '2026-04-01', '2026-04-02', '2026-04-05', NULL, NULL, 'authority_to_travel', 51, '2026-03-31 16:29:30', '2026-03-31 16:29:30'),
(305, '108435140100', 'Lyka Jane Leosala', 'Locator Slip', '2026-04-02', '2026-04-04', '2026-04-04', '13:00:00', '13:00:00', 'locator_slips', 34, '2026-04-02 04:00:45', '2026-04-02 04:00:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `todtr`
--
ALTER TABLE `todtr`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_source` (`source_table`,`source_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `todtr`
--
ALTER TABLE `todtr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=306;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
