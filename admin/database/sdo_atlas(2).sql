-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2026 at 03:18 AM
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
-- Database: `sdo_atlas`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action_type` varchar(100) DEFAULT NULL,
  `entity_type` varchar(50) DEFAULT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action_type`, `entity_type`, `entity_id`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:06:15'),
(2, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:06:17'),
(3, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:07:25'),
(4, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:09:10'),
(5, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:09:59'),
(6, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:12:30'),
(7, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:12:48'),
(8, 1, 'create_user', 'user', 2, 'Created user: jb@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:19:06'),
(9, 1, 'update_user', 'user', 1, 'Updated user: joerenz.dev@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:24:16'),
(10, 1, 'update_user', 'user', 2, 'Updated user: jb@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:24:22'),
(11, 1, 'update_profile', 'user', 1, 'Updated profile', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:24:40'),
(12, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:26:11'),
(13, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:27:51'),
(14, 1, 'approve_user', 'user', 5, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:28:16'),
(15, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:28:35'),
(16, 5, 'create', 'locator_slip', 1, 'Created Locator Slip: LS-2026-000001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:29:23'),
(17, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:34:31'),
(18, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:34:34'),
(19, 2, 'approve', 'locator_slip', 1, 'Approved Locator Slip: LS-2026-000001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:42:41'),
(20, 2, 'download', 'locator_slip', 1, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:53:37'),
(21, 5, 'create', 'locator_slip', 2, 'Created Locator Slip: LS-2026-000002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:02:15'),
(22, 5, 'create', 'locator_slip', 3, 'Created Locator Slip: LS-2026-000003', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:02:30'),
(23, 2, 'approve', 'locator_slip', 2, 'Approved Locator Slip: LS-2026-000002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:37:40'),
(24, 2, 'download', 'locator_slip', 2, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:37:44'),
(25, 2, 'approve', 'locator_slip', 3, 'Approved Locator Slip: LS-2026-000003', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:41:47'),
(26, 2, 'download', 'locator_slip', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:41:49'),
(27, 5, 'create', 'locator_slip', 4, 'Created Locator Slip: LS-2026-000004', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:44:51'),
(28, 2, 'approve', 'locator_slip', 4, 'Approved Locator Slip: LS-2026-000004', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:44:58'),
(29, 2, 'download', 'locator_slip', 4, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:45:00'),
(30, 5, 'create', 'locator_slip', 5, 'Created Locator Slip: LS-2026-000005', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:48:11'),
(31, 2, 'approve', 'locator_slip', 5, 'Approved Locator Slip: LS-2026-000005', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:48:22'),
(32, 2, 'download', 'locator_slip', 5, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:48:23'),
(33, 5, 'create', 'locator_slip', 6, 'Created Locator Slip: LS-2026-000006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:49:30'),
(34, 2, 'approve', 'locator_slip', 6, 'Approved Locator Slip: LS-2026-000006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:49:37'),
(35, 2, 'download', 'locator_slip', 6, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:49:39'),
(36, 5, 'create', 'locator_slip', 7, 'Created Locator Slip: LS-2026-000007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:56:25'),
(37, 2, 'approve', 'locator_slip', 7, 'Approved Locator Slip: LS-2026-000007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:56:31'),
(38, 2, 'download', 'locator_slip', 7, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:56:32'),
(39, 5, 'create', 'locator_slip', 8, 'Created Locator Slip: LS-2026-000008', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:57:32'),
(40, 2, 'approve', 'locator_slip', 8, 'Approved Locator Slip: LS-2026-000008', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:57:43'),
(41, 2, 'download', 'locator_slip', 8, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:57:45'),
(42, 2, 'download', 'locator_slip', 8, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 15:59:52'),
(43, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 00:01:56'),
(44, 5, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 00:02:00'),
(45, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 00:02:03'),
(46, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 00:02:20'),
(47, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 00:28:13'),
(48, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-22 00:32:50'),
(49, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 00:33:30'),
(50, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 00:33:36'),
(51, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-22 00:34:23'),
(52, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 01:40:59'),
(53, 5, 'download', 'locator_slip', 8, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 01:44:08'),
(54, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 03:31:40'),
(55, 5, 'create', 'authority_to_travel', 1, 'Created AT: AT-NATL-2026-000001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 03:32:45'),
(56, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 03:32:52'),
(57, 2, 'approve', 'authority_to_travel', 1, 'Approved AT: AT-NATL-2026-000001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 03:33:46'),
(58, 2, 'download', 'authority_to_travel', 1, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 03:33:47'),
(59, 5, 'create', 'authority_to_travel', 2, 'Created AT: AT-PERS-2026-000001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 03:34:32'),
(60, 2, 'approve', 'authority_to_travel', 2, 'Approved AT: AT-PERS-2026-000001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 03:34:56'),
(61, 2, 'download', 'authority_to_travel', 2, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 03:34:58'),
(62, 5, 'create', 'authority_to_travel', 3, 'Created AT: AT-LOCAL-2026-000001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 03:55:41'),
(63, 2, 'approve', 'authority_to_travel', 3, 'Approved AT: AT-LOCAL-2026-000001', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 03:55:49'),
(64, 2, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 03:55:50'),
(65, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 03:59:54'),
(66, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 04:00:53'),
(67, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 04:01:09'),
(68, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 04:01:44'),
(69, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:10:58'),
(70, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:10:58'),
(71, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:11:11'),
(72, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:11:11'),
(73, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:11:19'),
(74, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:11:19'),
(75, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:12:04'),
(76, 5, 'download', 'authority_to_travel', 1, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:12:41'),
(77, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:14:54'),
(78, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:18:06'),
(79, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:20:22'),
(80, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:23:47'),
(81, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:26:23'),
(82, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 05:28:54'),
(83, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:14:21'),
(84, 2, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:14:41'),
(85, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:14:48'),
(86, 1, 'create_user', 'user', 6, 'Created user: pj@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:16:01'),
(87, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:16:27'),
(88, 5, 'create', 'authority_to_travel', 4, 'Created AT: AT-PERS-2026-000002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:19:02'),
(89, 6, 'approve', 'authority_to_travel', 4, 'Approved AT: AT-PERS-2026-000002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:19:21'),
(90, 6, 'download', 'authority_to_travel', 4, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:19:23'),
(91, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:19:48'),
(92, 6, 'download', 'authority_to_travel', 4, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:20:04'),
(93, 5, 'create', 'authority_to_travel', 5, 'Created AT: AT-NATL-2026-000002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:58:03'),
(94, 6, 'approve', 'authority_to_travel', 5, 'Approved AT: AT-NATL-2026-000002', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:58:59'),
(95, 6, 'download', 'authority_to_travel', 5, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 06:59:08'),
(96, 5, 'download', 'authority_to_travel', 5, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 07:01:06'),
(97, 5, 'create', 'locator_slip', 9, 'Created Locator Slip: LS-2026-000009', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 07:03:07'),
(98, 5, 'create', 'authority_to_travel', 6, 'Created AT: AT-NATL-2026-000003', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 07:05:41'),
(99, 6, 'approve', 'authority_to_travel', 6, 'Approved AT: AT-NATL-2026-000003', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 07:06:19'),
(100, 6, 'download', 'authority_to_travel', 6, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 07:06:38'),
(101, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:10:11'),
(102, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:10:20'),
(103, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:10:28'),
(104, 2, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:10:36'),
(105, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:10:38'),
(106, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:11:06'),
(107, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:13:08'),
(108, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:13:35'),
(109, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:13:39'),
(110, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:14:07'),
(111, 1, 'create_user', 'user', 7, 'Created user: cidchief@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:46:22'),
(112, 1, 'create_user', 'user', 8, 'Created user: byrd@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:48:29'),
(113, 1, 'create_user', 'user', 9, 'Created user: escall.dev027@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:50:02'),
(114, 1, 'create_user', 'user', 10, 'Created user: loveresalgen@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:50:59'),
(115, 5, 'CREATE_AT', 'AT', 7, 'Created AT: AT-2026-000004', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:52:28'),
(116, 5, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:53:26'),
(117, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:53:30'),
(118, 7, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:53:32'),
(119, 8, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:53:36'),
(120, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:56:03'),
(121, 7, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:56:12'),
(122, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:56:18'),
(123, 1, 'update_user', 'user', 5, 'Updated user: redginepinedes09@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:56:53'),
(124, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 13:57:45'),
(125, 5, 'CREATE_AT', 'AT', 8, 'Created AT: AT-2026-000005', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:08:01'),
(126, 7, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:12:46'),
(127, 8, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:12:48'),
(128, 8, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:12:56'),
(129, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:13:02'),
(130, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:13:44'),
(131, 7, 'APPROVE_AT', 'AT', 8, 'Approved AT: AT-2026-000005', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:14:35'),
(132, 5, 'download', 'authority_to_travel', 8, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:15:03'),
(133, 5, 'CREATE_AT', 'AT', 9, 'Created AT: AT-2026-000006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:24:26'),
(134, 5, 'download', 'authority_to_travel', 8, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:29:24'),
(135, 5, 'download', 'authority_to_travel', 6, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:30:11'),
(136, 5, 'download', 'authority_to_travel', 3, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:31:35'),
(137, 7, 'APPROVE_AT', 'AT', 9, 'Approved AT: AT-2026-000006', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:35:16'),
(138, 7, 'download', 'authority_to_travel', 9, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:35:24'),
(139, 5, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:41:36'),
(140, 9, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:41:46'),
(141, 9, 'CREATE_AT', 'AT', 10, 'Created AT: AT-2026-000007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:43:58'),
(142, 6, 'APPROVE_AT', 'AT', 10, 'Approved AT: AT-2026-000007', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:44:58'),
(143, 6, 'download', 'authority_to_travel', 10, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:45:01'),
(144, 9, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:46:18'),
(145, 10, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:46:22'),
(146, 10, 'CREATE_AT', 'AT', 11, 'Created AT: AT-2026-000008', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:46:49'),
(147, 8, 'APPROVE_AT', 'AT', 11, 'Approved AT: AT-2026-000008', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:47:20'),
(148, 10, 'download', 'authority_to_travel', 11, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 14:47:31'),
(149, 10, 'create', 'locator_slip', 10, 'Created Locator Slip: LS-2026-000010', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:05:02'),
(150, 6, 'approve', 'locator_slip', 10, 'Approved Locator Slip: LS-2026-000010', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:09:59'),
(151, 10, 'download', 'locator_slip', 10, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:10:25'),
(152, 10, 'download', 'locator_slip', 10, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:11:49'),
(153, 8, 'download', 'locator_slip', 10, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:12:20'),
(154, 2, 'download', 'locator_slip', 10, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:12:37'),
(155, 10, 'create', 'locator_slip', 11, 'Created Locator Slip: LS-2026-000011', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:14:01'),
(156, 2, 'approve', 'locator_slip', 11, 'Approved Locator Slip: LS-2026-000011', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:14:13'),
(157, 2, 'download', 'locator_slip', 11, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:14:15'),
(158, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:53:48'),
(159, 9, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:56:47'),
(160, 9, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:56:54'),
(161, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-22 15:56:58'),
(162, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 00:46:53'),
(163, 9, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 01:51:30'),
(164, 9, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 01:51:34'),
(165, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 01:51:43'),
(166, 5, 'CREATE_AT', 'AT', 12, 'Created AT: AT-2026-000009', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 01:52:27'),
(167, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 01:52:46'),
(168, 8, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 01:52:58'),
(169, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 01:53:49'),
(170, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 01:57:31');

-- --------------------------------------------------------

--
-- Table structure for table `admin_roles`
--

CREATE TABLE `admin_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `permissions` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`permissions`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_roles`
--

INSERT INTO `admin_roles` (`id`, `role_name`, `description`, `permissions`, `is_active`, `created_at`) VALUES
(1, 'SUPERADMIN', 'Schools Division Superintendent - Full system access and executive override', '{\"all\": true}', 1, '2026-01-21 13:54:36'),
(2, 'ASDS', 'Assistant Schools Division Superintendent - Final approver for all travel requests', '{\"requests.view\": true, \"requests.approve\": true, \"requests.final_approve\": true, \"logs.view\": true, \"analytics.view\": true}', 1, '2026-01-21 13:54:36'),
(3, 'OSDS_CHIEF', 'Administrative Officer V - Recommending authority for OSDS units (Supply, Records, HR, Admin)', '{\"requests.view\": true, \"requests.recommend\": true, \"requests.own\": true, \"logs.view\": true}', 1, '2026-01-21 13:54:36'),
(4, 'CID_CHIEF', 'Chief, Curriculum Implementation Division - Recommending authority for CID', '{\"requests.view\": true, \"requests.recommend\": true, \"requests.own\": true, \"logs.view\": true}', 1, '2026-01-21 13:54:36'),
(5, 'SGOD_CHIEF', 'Chief, School Governance and Operations Division - Recommending authority for SGOD', '{\"requests.view\": true, \"requests.recommend\": true, \"requests.own\": true, \"logs.view\": true}', 1, '2026-01-22 13:42:20'),
(6, 'USER', 'SDO Employee - Can file and track own requests', '{\"requests.file\": true, \"requests.own\": true}', 1, '2026-01-22 13:42:20');

-- --------------------------------------------------------

--
-- Table structure for table `admin_users`
--

CREATE TABLE `admin_users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `employee_no` varchar(50) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `employee_position` varchar(100) DEFAULT NULL,
  `employee_office` varchar(150) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `google_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','active','inactive') DEFAULT 'pending',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_users`
--

INSERT INTO `admin_users` (`id`, `role_id`, `employee_no`, `full_name`, `employee_position`, `employee_office`, `email`, `password_hash`, `avatar_url`, `google_id`, `status`, `is_active`, `last_login`, `created_by`, `created_at`) VALUES
(1, 1, '108435140100', 'Alexander Joerenz Escallente', 'Superadmin', 'SDS', 'joerenz.dev@gmail.com', '$2y$10$i9CNq.Jmk./rwMFDdjmyLeCfp6xaYmHyczadTu5Ppo8p6ZJrwxQDm', NULL, NULL, 'active', 1, '2026-01-23 09:57:31', NULL, '2026-01-21 13:54:36'),
(2, 2, NULL, 'Joe-Bren L. Consuelo', 'ASDS', 'OSDS', 'jb@deped.com', '$2y$10$S/mKu96V0EW6va5s/7DB8.2vi/FGkjjanXPheT/QnSdPa55jesJa.', NULL, NULL, 'active', 1, '2026-01-22 21:14:07', 1, '2026-01-21 14:19:06'),
(5, 6, NULL, 'Redgine Pinedes', 'Teacher II', 'CID', 'redginepinedes09@gmail.com', '$2y$10$nkw03ywO9gVfOBC7jfASBuNK7TPbvq2EuS6BRWq5YXaPmn9QyVGEa', NULL, NULL, 'active', 1, '2026-01-23 09:51:43', NULL, '2026-01-21 14:27:40'),
(6, 3, NULL, 'Paul Jeremy I. Aguja', 'Administrative Office V', 'OSDS', 'pj@deped.com', '$2y$10$tzAnD5Ux6jaAUJC6oHbYa.7zjGAOBPqnqpQGrOu8UAcfFwrhzW.tq', NULL, NULL, 'active', 1, '2026-01-23 09:53:49', 1, '2026-01-22 06:16:01'),
(7, 4, NULL, 'Erma S. Valenzuela', 'CID - CHIEF', 'CID', 'cidchief@deped.com', '$2y$10$5akgjur7Fkzx04XpJT6Be.DfJv0Bz.3q90nOeUhEncaPxOm9s6F8K', NULL, NULL, 'active', 1, '2026-01-23 09:52:46', 1, '2026-01-22 13:46:22'),
(8, 5, NULL, 'Frederick G. Byrd Jr.', 'SGOD - CHIEF', 'SGOD', 'byrd@deped.com', '$2y$10$ZqdbL0T3LaLABt5GSplvZ.WRaLUv.DEim2QYm2.IsOLPmwZmMVfUu', NULL, NULL, 'active', 1, '2026-01-23 09:52:58', 1, '2026-01-22 13:48:29'),
(9, 6, NULL, 'Alexander Joerenz Escallente', 'ITO - 1', 'OSDS', 'escall.dev027@gmail.com', '$2y$10$1F5CMA6ZElJotpky9Mu48OHh.f4eUNkoURm1MB1prSCEJQj.26g02', NULL, NULL, 'active', 1, '2026-01-23 09:51:30', 1, '2026-01-22 13:50:02'),
(10, 6, NULL, 'Algen Loveres', 'PDO - I', 'SGOD', 'loveresalgen@gmail.com', '$2y$10$hyA/cvQZ82kDzfcwJjGbkuuJh19XeY1xpEZT5XBPCdg9X.xJOSHHK', NULL, NULL, 'active', 1, '2026-01-22 22:46:22', 1, '2026-01-22 13:50:59');

-- --------------------------------------------------------

--
-- Table structure for table `authority_to_travel`
--

CREATE TABLE `authority_to_travel` (
  `id` int(11) NOT NULL,
  `at_tracking_no` varchar(30) NOT NULL,
  `employee_name` varchar(150) NOT NULL,
  `employee_position` varchar(100) DEFAULT NULL,
  `permanent_station` varchar(150) DEFAULT NULL,
  `purpose_of_travel` text NOT NULL,
  `host_of_activity` varchar(255) DEFAULT NULL,
  `date_from` date NOT NULL,
  `date_to` date NOT NULL,
  `destination` varchar(255) NOT NULL,
  `fund_source` varchar(150) DEFAULT NULL,
  `inclusive_dates` varchar(150) DEFAULT NULL,
  `requesting_employee_name` varchar(150) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `recommending_authority_name` varchar(150) DEFAULT NULL,
  `recommending_date` date DEFAULT NULL,
  `approving_authority_name` varchar(150) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `travel_category` enum('official','personal') NOT NULL DEFAULT 'official',
  `travel_scope` enum('local','national') DEFAULT NULL,
  `status` enum('pending','recommended','approved','rejected') DEFAULT 'pending',
  `user_id` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `recommended_by` int(11) DEFAULT NULL,
  `current_approver_role` varchar(50) DEFAULT NULL,
  `routing_stage` enum('recommending','final','completed') DEFAULT 'recommending',
  `requester_office` varchar(150) DEFAULT NULL,
  `requester_role_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authority_to_travel`
--

INSERT INTO `authority_to_travel` (`id`, `at_tracking_no`, `employee_name`, `employee_position`, `permanent_station`, `purpose_of_travel`, `host_of_activity`, `date_from`, `date_to`, `destination`, `fund_source`, `inclusive_dates`, `requesting_employee_name`, `request_date`, `recommending_authority_name`, `recommending_date`, `approving_authority_name`, `approval_date`, `rejection_reason`, `travel_category`, `travel_scope`, `status`, `user_id`, `approved_by`, `created_at`, `updated_at`, `recommended_by`, `current_approver_role`, `routing_stage`, `requester_office`, `requester_role_id`) VALUES
(1, 'AT-NATL-2026-000001', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'gutom', 'sdo', '2026-01-22', '2026-01-22', 'Batanes', 'local', NULL, 'Redgine Pinedes', '2026-01-22', 'Joe-Bren L. Consuelo', '2026-01-22', 'Joe-Bren L. Consuelo', '2026-01-22', NULL, 'official', 'national', 'approved', 5, 2, '2026-01-22 03:32:45', '2026-01-22 13:42:20', NULL, NULL, 'completed', NULL, NULL),
(2, 'AT-PERS-2026-000001', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'ggs', '', '2026-01-22', '2026-01-22', 'Sta. Cruz, Laguna', '', NULL, 'Redgine Pinedes', '2026-01-22', 'Joe-Bren L. Consuelo', '2026-01-22', 'Joe-Bren L. Consuelo', '2026-01-22', NULL, 'personal', NULL, 'approved', 5, 2, '2026-01-22 03:34:32', '2026-01-22 13:42:20', NULL, NULL, 'completed', NULL, NULL),
(3, 'AT-LOCAL-2026-000001', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'MEETING', 'REGION IV-A', '2026-01-22', '2026-01-22', 'Batanes', 'OWNED', NULL, 'Redgine Pinedes', '2026-01-22', 'Joe-Bren L. Consuelo', '2026-01-22', 'Joe-Bren L. Consuelo', '2026-01-22', NULL, 'official', 'local', 'approved', 5, 2, '2026-01-22 03:55:41', '2026-01-22 13:42:20', NULL, NULL, 'completed', NULL, NULL),
(4, 'AT-PERS-2026-000002', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'Coffee', '', '2026-01-22', '2026-01-25', 'Festival', '', NULL, 'Redgine Pinedes', '2026-01-22', 'Paul Jeremy I. Aguja', '2026-01-22', 'Paul Jeremy I. Aguja', '2026-01-22', NULL, 'personal', NULL, 'approved', 5, 6, '2026-01-22 06:19:02', '2026-01-22 13:42:20', NULL, NULL, 'completed', NULL, NULL),
(5, 'AT-NATL-2026-000002', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'coffee', 'REGION IV-A', '2026-01-22', '2026-01-22', 'Festival', 'OWNED', NULL, 'Redgine Pinedes', '2026-01-22', 'Paul Jeremy I. Aguja', '2026-01-22', 'Paul Jeremy I. Aguja', '2026-01-22', NULL, 'official', 'national', 'approved', 5, 6, '2026-01-22 06:58:03', '2026-01-22 13:42:20', NULL, NULL, 'completed', NULL, NULL),
(6, 'AT-NATL-2026-000003', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'meeting', 'REGION IV-A', '2026-01-22', '2026-01-22', 'Batanes', 'local', NULL, 'Redgine Pinedes', '2026-01-22', 'Paul Jeremy I. Aguja', '2026-01-22', 'Paul Jeremy I. Aguja', '2026-01-22', NULL, 'official', 'national', 'approved', 5, 6, '2026-01-22 07:05:41', '2026-01-22 13:42:20', NULL, NULL, 'completed', NULL, NULL),
(7, 'AT-2026-000004', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'ORIENTATION OF MILK', 'SDO SAN PEDRO CITY', '2026-01-23', '2026-01-29', 'Alaska', 'MOOE', NULL, 'Redgine Pinedes', '2026-01-22', NULL, NULL, NULL, NULL, NULL, 'official', 'local', 'pending', 5, NULL, '2026-01-22 13:52:28', '2026-01-22 13:52:28', NULL, 'ASDS', 'final', 'CID', 4),
(8, 'AT-2026-000005', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'ojt', 'SDO SAN PEDRO CITY', '2026-01-22', '2026-01-29', 'Alaska', 'MOOE', NULL, 'Redgine Pinedes', '2026-01-22', 'CID Chief', '2026-01-22', 'CID Chief', '2026-01-22', NULL, 'official', 'local', 'approved', 5, 7, '2026-01-22 14:08:01', '2026-01-22 14:14:35', 7, NULL, 'completed', 'CID', 6),
(9, 'AT-2026-000006', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'bvifuytfytdytd', 'REGION IV-A', '2026-01-22', '2026-01-23', 'Sta. Cruz, Laguna', 'MOOE', NULL, 'Redgine Pinedes', '2026-01-22', 'Erma S. Valenzuela, CID Chief', '2026-01-22', 'Erma S. Valenzuela, CID Chief', '2026-01-22', NULL, 'official', 'local', 'approved', 5, 7, '2026-01-22 14:24:26', '2026-01-22 14:35:16', 7, NULL, 'completed', 'CID', 6),
(10, 'AT-2026-000007', 'Alexander Joerenz Escallente', 'ITO - 1', 'SDO San Pedro City', 'MAGBABAWAS', 'SDO SAN PEDRO CITY', '2026-01-22', '2026-01-23', 'BAHAY', 'MOOE', NULL, 'Alexander Joerenz Escallente', '2026-01-22', 'Paul Jeremy I. Aguja, AO V', '2026-01-22', 'Paul Jeremy I. Aguja, AO V', '2026-01-22', NULL, 'official', 'local', 'approved', 9, 6, '2026-01-22 14:43:58', '2026-01-22 14:44:58', 6, NULL, 'completed', 'OSDS', 6),
(11, 'AT-2026-000008', 'Algen Loveres', 'PDO - I', 'SDO San Pedro City', 'VACATION', '', '2026-01-22', '2026-01-28', 'VIETNAM', '', NULL, 'Algen Loveres', '2026-01-22', 'Frederick G. Byrd Jr., SGOD Chief', '2026-01-22', 'Frederick G. Byrd Jr., SGOD Chief', '2026-01-22', NULL, 'personal', NULL, 'approved', 10, 8, '2026-01-22 14:46:49', '2026-01-22 14:47:20', 8, NULL, 'completed', 'SGOD', 6),
(12, 'AT-2026-000009', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'dfdfsfdsfs', 'REGION IV-A', '2026-01-23', '2026-01-31', 'Sta. Cruz, Laguna', 'local', NULL, 'Redgine Pinedes', '2026-01-23', NULL, NULL, NULL, NULL, NULL, 'official', 'local', 'pending', 5, NULL, '2026-01-23 01:52:27', '2026-01-23 01:52:27', NULL, 'CID_CHIEF', 'recommending', 'CID', 6);

-- --------------------------------------------------------

--
-- Table structure for table `locator_slips`
--

CREATE TABLE `locator_slips` (
  `id` int(11) NOT NULL,
  `ls_control_no` varchar(30) NOT NULL,
  `employee_name` varchar(150) NOT NULL,
  `employee_position` varchar(100) DEFAULT NULL,
  `employee_office` varchar(150) DEFAULT NULL,
  `purpose_of_travel` text NOT NULL,
  `travel_type` varchar(50) NOT NULL,
  `date_time` datetime NOT NULL,
  `destination` varchar(255) NOT NULL,
  `requesting_employee_name` varchar(150) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `approver_name` varchar(150) DEFAULT NULL,
  `approver_position` varchar(100) DEFAULT NULL,
  `approval_date` date DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `user_id` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locator_slips`
--

INSERT INTO `locator_slips` (`id`, `ls_control_no`, `employee_name`, `employee_position`, `employee_office`, `purpose_of_travel`, `travel_type`, `date_time`, `destination`, `requesting_employee_name`, `request_date`, `approver_name`, `approver_position`, `approval_date`, `rejection_reason`, `status`, `user_id`, `approved_by`, `created_at`, `updated_at`) VALUES
(1, 'LS-2026-000001', 'Redgine Pinedes', 'Teacher II', 'CID', 'Meeting For Orientation', 'official', '2026-01-21 15:29:00', 'Sta. Cruz, Laguna', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'ASDS', '2026-01-21', NULL, 'approved', 5, 2, '2026-01-21 14:29:23', '2026-01-21 14:42:41'),
(2, 'LS-2026-000002', 'Redgine Pinedes', 'Teacher II', 'CID', 'Meeting For Orientation', 'official', '2026-01-21 15:29:00', 'Sta. Cruz, Laguna', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'ASDS', '2026-01-21', NULL, 'approved', 5, 2, '2026-01-21 15:02:15', '2026-01-21 15:37:40'),
(3, 'LS-2026-000003', 'Redgine Pinedes', 'Teacher II', 'CID', 'Meeting For Orientation', 'official', '2026-01-21 15:29:00', 'Sta. Cruz, Laguna', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'ASDS', '2026-01-21', NULL, 'approved', 5, 2, '2026-01-21 15:02:30', '2026-01-21 15:41:47'),
(4, 'LS-2026-000004', 'Redgine Pinedes', 'Teacher II', 'CID', 'gagala', 'official_time', '2026-01-21 16:41:00', 'Batanes', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'ASDS', '2026-01-21', NULL, 'approved', 5, 2, '2026-01-21 15:44:51', '2026-01-21 15:44:58'),
(5, 'LS-2026-000005', 'Redgine Pinedes', 'Teacher II', 'CID', 'secret', 'official_business', '2026-01-21 16:44:00', 'Batanes', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'ASDS', '2026-01-21', NULL, 'approved', 5, 2, '2026-01-21 15:48:11', '2026-01-21 15:48:22'),
(6, 'LS-2026-000006', 'Redgine Pinedes', 'Teacher II', 'CID', 'sdoo', 'official_business', '2026-01-21 16:48:00', 'Sta. Cruz, Laguna', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'ASDS', '2026-01-21', NULL, 'approved', 5, 2, '2026-01-21 15:49:30', '2026-01-21 15:49:37'),
(7, 'LS-2026-000007', 'Redgine Pinedes', 'Teacher II', 'CID', 'NAGUGUTOM', 'official_business', '2026-01-21 16:56:00', 'SDO', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'Assistant Schools Division Superintendent', '2026-01-21', NULL, 'approved', 5, 2, '2026-01-21 15:56:25', '2026-01-21 15:56:31'),
(8, 'LS-2026-000008', 'Redgine Pinedes', 'Teacher II', 'CID', 'NAGUGUTOM', 'official_business', '2026-01-21 16:56:00', 'SDO', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'Assistant Schools Division Superintendent', '2026-01-21', NULL, 'approved', 5, 2, '2026-01-21 15:57:32', '2026-01-21 15:57:43'),
(9, 'LS-2026-000009', 'Redgine Pinedes', 'Teacher II', 'CID', 'coffee\r\n', 'official_business', '2026-01-22 08:02:00', 'Sta. Cruz, Laguna', 'Redgine Pinedes', '2026-01-22', NULL, NULL, NULL, NULL, 'pending', 5, NULL, '2026-01-22 07:03:07', '2026-01-22 07:03:07'),
(10, 'LS-2026-000010', 'Algen Loveres', 'PDO - I', 'SGOD', 'FORGOT SOMETHING', 'official_business', '2026-01-22 10:04:00', 'BAHAY', 'Algen Loveres', '2026-01-22', 'Paul Jeremy I. Aguja', 'Administrative Officer V', '2026-01-22', NULL, 'approved', 10, 6, '2026-01-22 15:05:02', '2026-01-22 15:09:59'),
(11, 'LS-2026-000011', 'Algen Loveres', 'PDO - I', 'SGOD', 'may babalikan lang', 'official_time', '2026-01-22 10:00:00', 'BAHAY', 'Algen Loveres', '2026-01-22', 'Joe-Bren L. Consuelo', 'Assistant Schools Division Superintendent', '2026-01-22', NULL, 'approved', 10, 2, '2026-01-22 15:14:01', '2026-01-22 15:14:13');

-- --------------------------------------------------------

--
-- Table structure for table `session_tokens`
--

CREATE TABLE `session_tokens` (
  `id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_agent` varchar(500) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `session_tokens`
--

INSERT INTO `session_tokens` (`id`, `token`, `user_id`, `user_agent`, `ip_address`, `expires_at`, `created_at`) VALUES
(6, '418076efe4fe84ebf5a9c65ca7b6adfc502db3962f5df545b70f43327d90f823', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 01:09:04', '2026-01-21 14:28:35'),
(7, '39a09fb3f5b0516f3f3f249049bdde09ee70ca6636924dc12125f8bc04e4876a', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 01:09:09', '2026-01-21 14:34:34'),
(9, '9a244e824e59dfc81e110f1cfadbafb5f235f97afc15e5a6d8ffeeaf1ccdda7d', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 09:27:48', '2026-01-22 00:02:03'),
(10, '8cb0cf9132edbe0e67ac76054bc1bb4ca8490964462c416464aeabb0cf066c81', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 09:28:12', '2026-01-22 00:02:20'),
(11, '02a7fba1c9bf5b601d41a7bc096152aba8a39e8b5a297957ab1547a679551063', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 09:33:30', '2026-01-22 00:28:13'),
(12, 'f02ef825b8d0fd351a0b03e303f09f07095e11c8548bffb23c8ab883adad13c9', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '::1', '2026-01-22 09:37:11', '2026-01-22 00:32:50'),
(13, 'a56a6de672fc661ef8b0a23f3e58674045a30424bb2f05b1198b60e276c0ef2f', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 09:38:32', '2026-01-22 00:33:30'),
(14, '04f0d5f2d1674f1266685cd15b3b9b9a1aed821015d838bd2524951b56ef1eb8', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 09:33:36', '2026-01-22 00:33:36'),
(15, 'd6ed307014e96e772ffc06f85edba904f70c0c428d8cb3b7b71c4d09b3cdb9b0', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '::1', '2026-01-22 09:38:22', '2026-01-22 00:34:23'),
(16, '19b6325fdbca79c45410a954e87b46e758578cf0794b524f27fdcfb4922a1459', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 10:45:02', '2026-01-22 01:40:59'),
(17, '3508bba932d81e3485f9c6eff8035d47a0a4ca1cf1b5755005dfa090b95f23c1', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 13:01:44', '2026-01-22 03:31:40'),
(18, '7e4e70cfb14ce6cfdf4e1d8e17f7830c3b6defefadc4fad5214692d52f1c0687', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 12:55:50', '2026-01-22 03:32:52'),
(19, '34bd96824759b7309a2773dd566996c78f9a15014d327e0f6aa06a6a91f9a746', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 14:11:11', '2026-01-22 05:10:58'),
(20, '04ee9daf70e6e48223e75bb73befa0804ed7428587a524fdb62025b85d5952e5', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 14:11:19', '2026-01-22 05:11:11'),
(21, '43436831362182c3c51d4e94722b743463f381406275213d8c8dc408c6e821b8', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 14:12:04', '2026-01-22 05:11:19'),
(22, 'caea9847d28c04abac38a8f7dbca72e829c98f24f8c6bce8debb5298e2bb1a1e', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 16:07:50', '2026-01-22 05:12:04'),
(24, '5c0f5f35e4c67a7a4b01b33c1734e85f98e5938c852c8d96a0df2eea6b19cada', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 16:51:47', '2026-01-22 06:14:48'),
(25, '15ce3fdd3325135d6f1da2353dcbf5690fe4c37285d4bf24f1285b4bc02019d7', 6, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 16:07:05', '2026-01-22 06:16:27'),
(26, 'b6e4cbb065da11f91d36f4e27885cdfeda0f8e8530df42dfb7fd9ea32553ab1f', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 16:51:06', '2026-01-22 06:19:48'),
(29, '282870f7d3b2cc02d1ac2223ceeaa9dc56af43e4c0b64ba24f790edaa027e6b6', 6, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 00:05:53', '2026-01-22 13:10:28'),
(30, '6900ecf9980e1920a08f99ae74397c7def3a780497415e36107e07d5d36dcee8', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 22:11:06', '2026-01-22 13:10:38'),
(32, 'c4aee95f4bfb9dce9703e9f48c72fe665dbaec827f9a29a4fdd20bcaa1bb824f', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 22:13:35', '2026-01-22 13:13:08'),
(33, '50d0bd8bb783843c6cede39993f0c6c3d4c6bee52b05ea578d5ce2f26e4e7cf6', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 22:13:39', '2026-01-22 13:13:35'),
(34, '02f9b0c65c72c8096cedcf925c7a44f70cfb11a2ac953774808ea1316db896d2', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-22 22:14:07', '2026-01-22 13:13:39'),
(35, '25a0bfa94dd333dbccd2d4a8202db439cbf53c40d712bca8cf2080d438ddd726', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 00:14:15', '2026-01-22 13:14:07'),
(37, '753cf1eec66f42c2507e495c58192045007b304aa56c75157b45fb8650383602', 8, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 00:22:20', '2026-01-22 13:53:36'),
(42, '7ce8679b1db206dcbb04c3f1edf0e7e82a01c49610a5a1b3cf37c1a3941ea902', 6, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 00:25:42', '2026-01-22 14:13:02'),
(43, 'e4ff2b79653b899caa9f56161345047fe7155c6522e86d74bc32cb99a6b44300', 7, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 00:32:54', '2026-01-22 14:13:44'),
(45, '143b608092f136b09d213b099763d725f8650bd3252efbef52dedf7ea7be78a9', 10, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 00:15:18', '2026-01-22 14:46:22'),
(47, 'b9961f032a23cae3541aba95dfc18919032673eb544fa062e43355fe3a0d7080', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 00:57:23', '2026-01-22 15:56:58'),
(48, 'c793b068ebe691800dfcf048f80025ff68790d783c12cb677d89e675e75f418e', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 09:47:21', '2026-01-23 00:46:53'),
(50, 'a1b15a03f4e8f64f90ce574fe225ae24ff89fd02132c1fb03e75e186276c7400', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 10:52:46', '2026-01-23 01:51:43'),
(51, 'cac0be1044b48a81590556a1e6e8a17a5be94b88021d2733a0637784c21c5a3f', 7, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 10:53:08', '2026-01-23 01:52:46'),
(52, 'a87ffd721f879a06cf99653079654e9afe7a718544eacd9ae998cc918dcf7196', 8, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 10:53:49', '2026-01-23 01:52:58'),
(53, '0778785a19b0f4408da7c904c7bb369ee9439de9e0263dd022dd9708410955cc', 6, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 10:57:31', '2026-01-23 01:53:49'),
(54, '519a910bdc94b9d6e7d83bb6eb27f1db0c031d4524b1fd404be90f0737be0802', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 10:57:57', '2026-01-23 01:57:31');

-- --------------------------------------------------------

--
-- Table structure for table `tracking_sequences`
--

CREATE TABLE `tracking_sequences` (
  `id` int(11) NOT NULL,
  `prefix` varchar(20) NOT NULL,
  `year` int(11) NOT NULL,
  `last_number` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tracking_sequences`
--

INSERT INTO `tracking_sequences` (`id`, `prefix`, `year`, `last_number`) VALUES
(1, 'LS', 2026, 11),
(2, 'AT-LOCAL', 2026, 1),
(3, 'AT-NATL', 2026, 3),
(4, 'AT-PERS', 2026, 2),
(5, 'AT', 2026, 9);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `admin_roles`
--
ALTER TABLE `admin_roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `authority_to_travel`
--
ALTER TABLE `authority_to_travel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `at_tracking_no` (`at_tracking_no`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_category` (`travel_category`),
  ADD KEY `idx_scope` (`travel_scope`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_routing` (`current_approver_role`,`routing_stage`);

--
-- Indexes for table `locator_slips`
--
ALTER TABLE `locator_slips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ls_control_no` (`ls_control_no`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `session_tokens`
--
ALTER TABLE `session_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `tracking_sequences`
--
ALTER TABLE `tracking_sequences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `prefix` (`prefix`,`year`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT for table `admin_roles`
--
ALTER TABLE `admin_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `authority_to_travel`
--
ALTER TABLE `authority_to_travel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `locator_slips`
--
ALTER TABLE `locator_slips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `session_tokens`
--
ALTER TABLE `session_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `tracking_sequences`
--
ALTER TABLE `tracking_sequences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `admin_users`
--
ALTER TABLE `admin_users`
  ADD CONSTRAINT `admin_users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`);

--
-- Constraints for table `authority_to_travel`
--
ALTER TABLE `authority_to_travel`
  ADD CONSTRAINT `authority_to_travel_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`),
  ADD CONSTRAINT `authority_to_travel_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `locator_slips`
--
ALTER TABLE `locator_slips`
  ADD CONSTRAINT `locator_slips_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`),
  ADD CONSTRAINT `locator_slips_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `session_tokens`
--
ALTER TABLE `session_tokens`
  ADD CONSTRAINT `session_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
