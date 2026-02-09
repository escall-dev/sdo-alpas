-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 09, 2026 at 04:10 AM
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
(170, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 01:57:31'),
(171, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 02:43:34'),
(172, 5, 'create', 'locator_slip', 12, 'Created Locator Slip: LS-2026-000012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 02:44:44'),
(173, 7, 'approve', 'locator_slip', 12, 'Approved Locator Slip: LS-2026-000012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 02:45:26'),
(174, 7, 'download', 'locator_slip', 12, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 02:45:40'),
(175, 7, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 03:36:36'),
(176, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 03:36:39'),
(177, 7, 'create_oic', 'oic_delegation', 1, 'Created OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 03:44:17'),
(178, 7, 'deactivate_oic', 'oic_delegation', 1, 'Deactivated OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 03:44:28'),
(179, 7, 'create_oic', 'oic_delegation', 2, 'Created OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 03:44:39'),
(180, 8, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 03:51:24'),
(181, 8, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 03:51:28'),
(182, 9, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 05:15:25'),
(183, 9, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 05:15:27'),
(184, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 05:15:31'),
(185, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 05:16:42'),
(186, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 05:16:58'),
(187, 8, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 05:17:05'),
(188, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 05:17:15'),
(189, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:29:33'),
(190, 1, 'create_user', 'user', 11, 'Created user: cb@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:34:31'),
(191, 11, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:35:28'),
(192, 11, 'CREATE_AT', 'AT', 13, 'Created AT: AT-2026-000010', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:36:15'),
(193, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:37:03'),
(194, 1, 'update_user', 'user', 11, 'Updated user: cb@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:38:13'),
(195, 6, 'REJECT_AT', 'AT', 13, 'Rejected AT: AT-2026-000010', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:39:42'),
(196, 11, 'CREATE_AT', 'AT', 14, 'Created AT: AT-2026-000011', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:40:26'),
(197, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:42:16'),
(198, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:45:14'),
(199, 11, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:45:22'),
(200, 1, 'create_user', 'user', 12, 'Created user: jdt@gmail.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:47:01'),
(201, 12, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:47:22'),
(202, 12, 'CREATE_AT', 'AT', 15, 'Created AT: AT-2026-000012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 06:48:06'),
(203, 7, 'deactivate_oic', 'oic_delegation', 2, 'Deactivated OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 07:19:43'),
(204, 7, 'create_oic', 'oic_delegation', 3, 'Created OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 07:19:56'),
(205, 5, 'OIC-APPROVAL', 'AT', 12, 'Approved AT: AT-2026-000009', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 07:29:43'),
(206, 5, 'download', 'authority_to_travel', 12, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 07:29:51'),
(207, 7, 'deactivate_oic', 'oic_delegation', 3, 'Deactivated OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 08:16:23'),
(208, 7, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 08:22:35'),
(209, 5, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-23 08:22:48'),
(210, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:26:01'),
(211, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:26:08'),
(212, 8, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:26:26'),
(213, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:26:36'),
(214, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:26:46'),
(215, 7, 'APPROVE_AT', 'AT', 15, 'Approved AT: AT-2026-000012', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:30:52'),
(216, 7, 'download', 'authority_to_travel', 15, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:30:53'),
(217, 2, 'download', 'locator_slip', 12, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:36:25'),
(218, 1, 'approve_user', 'user', 13, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:39:24'),
(219, 13, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 01:39:29'),
(220, 13, 'CREATE_AT', 'AT', 16, 'Created AT: AT-2026-000013', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:33:08'),
(221, 5, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:33:50'),
(222, 11, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:33:56'),
(223, 11, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:33:58'),
(224, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:34:03'),
(225, 13, 'CREATE_AT', 'AT', 17, 'Created AT: AT-2026-000014', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:34:56'),
(226, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:37:29'),
(227, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 02:37:32'),
(228, 8, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:13:05'),
(229, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:13:17');
INSERT INTO `activity_logs` (`id`, `user_id`, `action_type`, `entity_type`, `entity_id`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(230, 1, 'update_user', 'user', 13, 'Updated user: ej@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:14:59'),
(231, 13, 'CREATE_AT', 'AT', 18, 'Created AT: AT-2026-000015', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:16:20'),
(232, 13, 'update_profile', 'user', 13, 'Updated profile', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:16:53'),
(233, 13, 'CREATE_AT', 'AT', 19, 'Created AT: AT-2026-000016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:17:16'),
(234, 9, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:18:27'),
(235, 9, 'CREATE_AT', 'AT', 20, 'Created AT: AT-2026-000017', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:18:47'),
(236, 13, 'update_profile', 'user', 13, 'Updated profile', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:19:58'),
(237, 1, 'update_user', 'user', 13, 'Updated user: ej@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:20:43'),
(238, 13, 'CREATE_AT', 'AT', 21, 'Created AT: AT-2026-000018', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:21:23'),
(239, 1, 'create_user', 'user', 14, 'Created user: lglasst@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:38:09'),
(240, 9, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:38:21'),
(241, 14, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:38:24'),
(242, 14, 'CREATE_AT', 'AT', 22, 'Created AT: AT-2026-000019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 03:38:43'),
(243, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:32:34'),
(244, 12, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:32:55'),
(245, 12, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:32:57'),
(246, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:33:03'),
(247, 6, 'APPROVE_AT', 'AT', 21, 'Approved AT: AT-2026-000018', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:33:13'),
(248, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:33:23'),
(249, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:33:45'),
(250, 6, 'download', 'authority_to_travel', 21, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:34:15'),
(251, 6, 'APPROVE_AT', 'AT', 14, 'Approved AT: AT-2026-000011', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:34:37'),
(252, 6, 'download', 'authority_to_travel', 14, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:34:38'),
(253, 6, 'download', 'authority_to_travel', 14, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:37:36'),
(254, 6, 'download', 'authority_to_travel', 14, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:38:26'),
(255, 6, 'download', 'authority_to_travel', 14, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:39:31'),
(256, 6, 'download', 'authority_to_travel', 14, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 05:42:17'),
(257, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 06:11:46'),
(258, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 06:11:50'),
(259, 1, 'toggle_unit_routing', 'unit_routing_config', 19, 'Toggled unit routing status', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 06:21:23'),
(260, 1, 'toggle_unit_routing', 'unit_routing_config', 19, 'Toggled unit routing status', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-26 06:21:26'),
(261, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 00:26:14'),
(262, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 00:38:33'),
(263, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 01:48:36'),
(264, 5, 'download', 'authority_to_travel', 12, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 01:58:24'),
(265, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 01:58:31'),
(266, 5, 'download', 'authority_to_travel', 9, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 01:59:20'),
(267, 5, 'download', 'locator_slip', 7, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 01:59:50'),
(268, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 02:44:18'),
(269, 6, 'download', 'authority_to_travel', 21, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 02:44:29'),
(270, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 02:45:16'),
(271, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 02:45:19'),
(272, 7, 'download', 'locator_slip', 12, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 02:45:32'),
(273, 7, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 02:46:00'),
(274, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 02:46:05'),
(275, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 05:23:49'),
(276, 1, 'update_user', 'user', 13, 'Updated user: ej@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 05:53:38'),
(277, 1, 'update_user', 'user', 12, 'Updated user: psds@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 05:55:13'),
(278, 1, 'update_user', 'user', 13, 'Updated user: ict1@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 05:56:46'),
(279, 1, 'update_user', 'user', 11, 'Updated user: acct@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 05:57:17'),
(280, 1, 'update_user', 'user', 10, 'Updated user: pdo@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 05:57:39'),
(281, 1, 'update_user', 'user', 9, 'Updated user: ito@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 05:58:07'),
(282, 1, 'update_user', 'user', 8, 'Updated user: sgdochief@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 05:58:36'),
(283, 1, 'update_user', 'user', 6, 'Updated user: aov@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 05:58:59'),
(284, 1, 'update_user', 'user', 5, 'Updated user: teacher@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 05:59:29'),
(285, 1, 'update_user', 'user', 13, 'Updated user: ictclerk1@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 06:06:09'),
(286, 1, 'update_user', 'user', 14, 'Updated user: lglasst@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 06:54:28'),
(287, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:15:49'),
(288, 1, 'update_user', 'user', 14, 'Updated user: lglasst@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:15:56'),
(289, 1, 'update_user', 'user', 14, 'Updated user: lglasst@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:16:10'),
(290, 1, 'update_user', 'user', 14, 'Updated user: lglasst@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:16:18'),
(291, 1, 'update_user', 'user', 14, 'Updated user: lglasst@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:16:35'),
(292, 1, 'update_user', 'user', 14, 'Updated user: lglasst@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:16:46'),
(293, 1, 'update_user', 'user', 13, 'Updated user: ictclerk1@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:17:42'),
(294, 1, 'update_user', 'user', 13, 'Updated user: ictclerk1@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:17:49'),
(295, 1, 'update_user', 'user', 13, 'Updated user: ictclerk1@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:17:54'),
(296, 1, 'update_user', 'user', 14, 'Updated user: lglasst@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:19:40'),
(297, 1, 'update_user', 'user', 14, 'Updated user: lglasst@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:19:48'),
(298, 1, 'update_user', 'user', 14, 'Updated user: lglasst@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:19:55'),
(299, 1, 'update_user', 'user', 14, 'Updated user: lglasst@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:20:07'),
(300, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:34:09'),
(301, 7, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:34:13'),
(302, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-27 08:34:40'),
(303, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-28 00:58:44'),
(304, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-28 00:58:51'),
(305, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-28 00:59:20'),
(306, 5, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-28 01:57:31'),
(307, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-28 01:57:35'),
(308, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-28 02:04:26'),
(309, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 00:14:21'),
(310, 1, 'update_user', 'user', 15, 'Updated user: abigailaaiii@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 01:37:57'),
(311, 1, 'update_user', 'user', 13, 'Updated user: ictclerk1@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 01:39:40'),
(312, 1, 'create_user', 'user', 23, 'Created user: michaelaoiii@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 01:55:21'),
(313, 1, 'create_user', 'user', 24, 'Created user: christianlsb@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 01:56:55'),
(314, 1, 'create_user', 'user', 25, 'Created user: micahlsb@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 01:58:04'),
(315, 1, 'create_user', 'user', 26, 'Created user: larrylsb@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 01:59:11'),
(316, 1, 'create_user', 'user', 27, 'Created user: junlsb@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 02:00:40'),
(317, 1, 'create_user', 'user', 28, 'Created user: jaypeeaaii@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 02:08:15'),
(318, 1, 'create_user', 'user', 29, 'Created user: arleneaavi@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 02:10:32'),
(319, 1, 'create_user', 'user', 30, 'Created user: marvinlsb@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 02:14:55'),
(320, 1, 'create_user', 'user', 31, 'Created user: shielaatty@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 02:16:23'),
(321, 1, 'create_user', 'user', 32, 'Created user: jrlasb@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 02:17:28'),
(322, 1, 'create_user', 'user', 33, 'Created user: laurenceseps@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 02:50:44'),
(323, 1, 'create_user', 'user', 34, 'Created user: orimarseps@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 02:52:15'),
(324, 1, 'create_user', 'user', 35, 'Created user: maryseps@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 02:53:31'),
(325, 1, 'create_user', 'user', 36, 'Created user: jeninadentist@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 02:58:09'),
(326, 1, 'create_user', 'user', 47, 'Created user: ernestoepsf@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 03:14:52'),
(327, 1, 'create_user', 'user', 48, 'Created user: brilsb@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 03:16:16'),
(328, 1, 'create_user', 'user', 49, 'Created user: juneepsals@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 03:17:29'),
(329, 1, 'create_user', 'user', 50, 'Created user: shirleypsds@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 03:18:38'),
(330, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 04:33:17'),
(331, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 04:36:13'),
(332, 1, 'download', 'locator_slip', 12, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 06:02:18'),
(333, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 07:53:29'),
(334, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-29 07:53:40'),
(335, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 00:34:28'),
(336, 1, 'approve_user', 'user', 45, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:04:57'),
(337, 45, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:05:08'),
(338, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:05:29'),
(339, 45, 'create', 'locator_slip', 13, 'Created Locator Slip: LS-2026-000013', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:09:51'),
(340, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:10:58'),
(341, 7, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:12:42'),
(342, 45, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:13:11'),
(343, 36, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:13:16'),
(344, 36, 'create', 'locator_slip', 14, 'Created Locator Slip: LS-2026-000014', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:13:33'),
(345, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:13:36'),
(346, 8, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:14:17'),
(347, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:14:33'),
(348, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:14:46'),
(349, 36, 'CREATE_AT', 'AT', 23, 'Created AT: AT-2026-000024', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:16:50'),
(350, 36, 'create', 'locator_slip', 15, 'Created Locator Slip: LS-2026-000015', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 01:38:04'),
(351, 36, 'create', 'locator_slip', 16, 'Created Locator Slip: LS-2026-000016', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 02:05:32'),
(352, 36, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 02:49:57'),
(353, 6, 'reject', 'locator_slip', 15, 'Rejected Locator Slip: LS-2026-000015', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 02:50:11'),
(354, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 02:50:32'),
(355, 7, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-30 02:50:42'),
(356, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 02:11:32'),
(357, 2, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 02:11:37'),
(358, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 02:11:41'),
(359, 2, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 02:43:55'),
(360, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 02:44:07'),
(361, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 03:40:38'),
(362, 6, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 04:34:51'),
(363, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 04:34:58'),
(364, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 08:02:18'),
(365, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-02 08:02:24'),
(366, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 00:40:28'),
(367, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 00:41:52'),
(368, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 03:27:58'),
(369, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:15:24'),
(370, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:15:28'),
(371, 2, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:30:19'),
(372, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:30:27'),
(373, 1, 'toggle_unit_routing', 'unit_routing_config', 1, 'Toggled unit routing status', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:44:10'),
(374, 1, 'toggle_unit_routing', 'unit_routing_config', 1, 'Toggled unit routing status', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:44:14'),
(375, 1, 'deactivate_user', 'user', 50, 'Deactivated user', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:47:31'),
(376, 1, 'deactivate_user', 'user', 50, 'Deactivated user', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:47:44'),
(377, 1, 'deactivate_user', 'user', 50, 'Deactivated user', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:47:46'),
(378, 1, 'deactivate_user', 'user', 50, 'Deactivated user', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:47:52'),
(379, 1, 'deactivate_user', 'user', 50, 'Deactivated user', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:49:28'),
(380, 1, 'activate_user', 'user', 50, 'Activated user', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:49:29'),
(381, 1, 'activate_user', 'user', 50, 'Activated user', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:49:35'),
(382, 1, 'update_profile', 'user', 1, 'Updated profile', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 05:57:03'),
(383, 1, 'update_profile', 'user', 1, 'Updated profile', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:01:47'),
(384, 1, 'update_profile', 'user', 1, 'Updated profile', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:02:27'),
(385, 1, 'update_profile', 'user', 1, 'Updated profile', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:02:32'),
(386, 1, 'update_profile', 'user', 1, 'Updated profile', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:02:38'),
(387, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:19:46'),
(388, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:24:36'),
(389, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:24:45'),
(390, 2, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:24:47'),
(391, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:24:53'),
(392, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:28:04'),
(393, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:28:07'),
(394, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:32:07'),
(395, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-03 06:34:21'),
(396, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 00:07:40'),
(397, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 00:15:37'),
(398, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 01:53:54'),
(399, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 01:56:46'),
(400, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 01:56:48'),
(401, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 01:57:54'),
(402, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:28:06'),
(403, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:36:12'),
(404, 1, 'approve_user', 'user', 44, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:38:28'),
(405, 1, 'approve_user', 'user', 43, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:38:31'),
(406, 1, 'approve_user', 'user', 42, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:38:32'),
(407, 1, 'approve_user', 'user', 41, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:38:33'),
(408, 1, 'approve_user', 'user', 40, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:38:34'),
(409, 1, 'approve_user', 'user', 39, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:38:35'),
(410, 1, 'approve_user', 'user', 38, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:38:36'),
(411, 1, 'approve_user', 'user', 37, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:38:38'),
(412, 1, 'approve_user', 'user', 22, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:47:51'),
(413, 1, 'approve_user', 'user', 21, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:47:52'),
(414, 1, 'approve_user', 'user', 20, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:47:54'),
(415, 1, 'approve_user', 'user', 19, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:47:55'),
(416, 1, 'approve_user', 'user', 18, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:47:56'),
(417, 1, 'approve_user', 'user', 17, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:47:57'),
(418, 1, 'approve_user', 'user', 16, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:47:58'),
(419, 1, 'approve_user', 'user', 16, 'Approved user registration', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:48:04'),
(420, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:48:08'),
(421, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:48:57'),
(422, 5, 'create', 'locator_slip', 17, 'Created Locator Slip: LS-2026-000017', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:49:36'),
(423, 5, 'CREATE_AT', 'AT', 24, 'Created AT: AT-2026-000025', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:52:18'),
(424, 5, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:52:22'),
(425, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:52:31'),
(426, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 02:54:52'),
(427, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 04:10:54'),
(428, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 06:38:18'),
(429, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:36:05'),
(430, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-04 13:43:21'),
(431, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 00:42:24'),
(432, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 00:59:02'),
(433, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 01:55:50'),
(434, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:50:08'),
(435, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:51:41'),
(436, 39, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:52:01'),
(437, 39, 'CREATE_AT', 'AT', 25, 'Created AT: AT-2026-000026', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:53:01'),
(438, 8, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:53:23'),
(439, 8, 'APPROVE_AT', 'AT', 25, 'Approved AT: AT-2026-000026', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:53:38'),
(440, 39, 'download', 'authority_to_travel', 25, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:54:37'),
(441, 8, 'create_oic', 'oic_delegation', 4, 'Created OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:56:21'),
(442, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:56:38'),
(443, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:56:55'),
(444, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:57:06'),
(445, 10, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:57:14'),
(446, 8, 'deactivate_oic', 'oic_delegation', 4, 'Deactivated OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:57:37'),
(447, 8, 'create_oic', 'oic_delegation', 5, 'Created OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 07:57:55'),
(448, 39, 'create', 'locator_slip', 18, 'Created Locator Slip: LS-2026-000018', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:01:13'),
(449, 10, 'OIC-APPROVAL', 'locator_slip', 18, 'Approved Locator Slip: LS-2026-000018', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:01:43'),
(450, 39, 'download', 'locator_slip', 18, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:01:58'),
(451, 39, 'download', 'locator_slip', 18, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:06:00'),
(452, 39, 'CREATE_AT', 'AT', 26, 'Created AT: AT-2026-000027', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:07:34'),
(453, 8, 'APPROVE_AT', 'AT', 26, 'Approved AT: AT-2026-000027', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:07:45'),
(454, 8, 'download', 'authority_to_travel', 26, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:07:51'),
(455, 39, 'download', 'locator_slip', 18, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:10:06'),
(456, 39, 'download', 'authority_to_travel', 25, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:11:00'),
(457, 39, 'download', 'locator_slip', 18, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:11:20'),
(458, 39, 'create', 'locator_slip', 19, 'Created Locator Slip: LS-2026-000019', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:11:47');
INSERT INTO `activity_logs` (`id`, `user_id`, `action_type`, `entity_type`, `entity_id`, `description`, `old_value`, `new_value`, `ip_address`, `user_agent`, `created_at`) VALUES
(459, 8, 'deactivate_oic', 'oic_delegation', 5, 'Deactivated OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:12:04'),
(460, 39, 'create', 'locator_slip', 20, 'Created Locator Slip: LS-2026-000020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:12:49'),
(461, 8, 'approve', 'locator_slip', 20, 'Approved Locator Slip: LS-2026-000020', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:12:58'),
(462, 8, 'download', 'locator_slip', 20, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:13:04'),
(463, 8, 'download', 'locator_slip', 20, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:14:21'),
(464, 8, 'download', 'locator_slip', 20, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:15:52'),
(465, 10, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:17:48'),
(466, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:17:51'),
(467, 1, 'deactivate_user', 'user', 50, 'Deactivated user', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:19:56'),
(468, 39, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:20:04'),
(469, 1, 'activate_user', 'user', 50, 'Activated user', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:20:23'),
(470, 50, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 08:33:35'),
(471, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 11:49:31'),
(472, 1, 'create_user', 'user', 54, 'Created user: sds@deped.com', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 11:50:38'),
(473, 54, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 11:51:03'),
(474, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:13:36'),
(475, 45, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:16:30'),
(476, 45, 'create', 'locator_slip', 21, 'Created Locator Slip: LS-2026-000021', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:18:10'),
(477, 6, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:18:31'),
(478, 6, 'approve', 'locator_slip', 21, 'Approved Locator Slip: LS-2026-000021', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:19:12'),
(479, 6, 'download', 'locator_slip', 21, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:23:41'),
(480, 6, 'download', 'locator_slip', 21, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:26:05'),
(481, 6, 'download', 'locator_slip', 21, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:30:58'),
(482, 6, 'download', 'locator_slip', 21, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:35:34'),
(483, 6, 'download', 'locator_slip', 21, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:36:50'),
(484, 6, 'download', 'locator_slip', 21, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:39:09'),
(485, 6, 'approve', 'locator_slip', 14, 'Approved Locator Slip: LS-2026-000014', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:42:04'),
(486, 6, 'download', 'locator_slip', 14, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:42:09'),
(487, 6, 'create', 'locator_slip', 22, 'Created Locator Slip: LS-2026-000022', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:43:23'),
(488, 6, 'update', 'locator_slip', 22, 'Updated Locator Slip: LS-2026-000022', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:43:48'),
(489, 6, 'create', 'locator_slip', 23, 'Created Locator Slip: LS-2026-000023', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:47:51'),
(490, 45, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:48:55'),
(491, 2, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:49:06'),
(492, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:51:30'),
(493, 45, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:53:31'),
(494, 45, 'CREATE_AT', 'AT', 27, 'Created AT: AT-2026-000028', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:54:06'),
(495, 7, 'RECOMMEND_AT', 'AT', 27, 'Recommended AT: AT-2026-000028', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:54:27'),
(496, 54, 'APPROVE_AT', 'AT', 27, 'Approved AT: AT-2026-000028', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:54:53'),
(497, 6, 'update', 'locator_slip', 23, 'Updated Locator Slip: LS-2026-000023', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:56:01'),
(498, 2, 'reject', 'locator_slip', 23, 'Rejected Locator Slip: LS-2026-000023', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:56:26'),
(499, 2, 'approve', 'locator_slip', 22, 'Approved Locator Slip: LS-2026-000022', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:57:12'),
(500, 6, 'download', 'locator_slip', 22, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 12:57:38'),
(501, 45, 'download', 'authority_to_travel', 27, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:01:36'),
(502, 45, 'download', 'authority_to_travel', 27, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:02:03'),
(503, 7, 'RECOMMEND_AT', 'AT', 24, 'Recommended AT: AT-2026-000025', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:23:44'),
(504, 45, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:23:50'),
(505, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:24:19'),
(506, 54, 'APPROVE_AT', 'AT', 24, 'Approved AT: AT-2026-000025', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:24:38'),
(507, 5, 'download', 'authority_to_travel', 24, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:24:57'),
(508, 5, 'download', 'authority_to_travel', 24, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:27:52'),
(509, 5, 'download', 'authority_to_travel', 24, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:29:19'),
(510, 5, 'download', 'authority_to_travel', 24, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:35:45'),
(511, 5, 'CREATE_AT', 'AT', 28, 'Created AT: AT-2026-000029', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:37:44'),
(512, 7, 'RECOMMEND_AT', 'AT', 28, 'Recommended AT: AT-2026-000029', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:37:56'),
(513, 54, 'APPROVE_AT', 'AT', 28, 'Approved AT: AT-2026-000029', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:38:04'),
(514, 5, 'download', 'authority_to_travel', 28, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:38:14'),
(515, 54, 'download', 'authority_to_travel', 28, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:40:13'),
(516, 7, 'create_oic', 'oic_delegation', 6, 'Created OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:42:33'),
(517, 6, 'create_oic', 'oic_delegation', 7, 'Created OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:50:22'),
(518, 7, 'deactivate_oic', 'oic_delegation', 6, 'Deactivated OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:50:32'),
(519, 5, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:50:38'),
(520, 9, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:50:58'),
(521, 6, 'CREATE_AT', 'AT', 29, 'Created AT: AT-2026-000030', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:53:38'),
(522, 6, 'UPDATE_AT', 'AT', 29, 'Updated AT: AT-2026-000030', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:53:56'),
(523, 54, 'APPROVE_AT', 'AT', 29, 'Approved AT: AT-2026-000030', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:54:14'),
(524, 6, 'download', 'authority_to_travel', 29, 'Downloaded document', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-05 13:54:30'),
(525, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:35:11'),
(526, 7, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:35:15'),
(527, 5, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:35:25'),
(528, 7, 'create_oic', 'oic_delegation', 8, 'Created OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:35:55'),
(529, 7, 'deactivate_oic', 'oic_delegation', 8, 'Deactivated OIC delegation', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 01:36:08'),
(530, 1, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 02:58:17'),
(531, 7, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 02:58:18'),
(532, 5, 'logout', 'auth', NULL, 'User logged out', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 02:58:20'),
(533, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-02-09 03:00:30');

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
(6, 'USER', 'SDO Employee - Can file and track own requests', '{\"requests.file\": true, \"requests.own\": true}', 1, '2026-01-22 13:42:20'),
(7, 'SDS', 'Schools Division Superintendent - Final approver for all travel requests', '{\"requests.view\": true, \"requests.final_approve\": true, \"logs.view\": true, \"analytics.view\": true}', 1, '2026-02-05 11:46:38');

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
  `office_id` int(11) DEFAULT NULL,
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

INSERT INTO `admin_users` (`id`, `role_id`, `employee_no`, `full_name`, `employee_position`, `employee_office`, `office_id`, `email`, `password_hash`, `avatar_url`, `google_id`, `status`, `is_active`, `last_login`, `created_by`, `created_at`) VALUES
(1, 1, '108435140100', 'Alexander Joerenz Escallente', 'Superadmin', 'ICT', 17, 'joerenz.dev@gmail.com', '$2y$10$i9CNq.Jmk./rwMFDdjmyLeCfp6xaYmHyczadTu5Ppo8p6ZJrwxQDm', NULL, NULL, 'active', 1, '2026-02-09 11:00:30', NULL, '2026-01-21 13:54:36'),
(2, 2, NULL, 'Joe-Bren L. Consuelo', 'ASDS', 'OSDS', 10, 'jb@deped.com', '$2y$10$S/mKu96V0EW6va5s/7DB8.2vi/FGkjjanXPheT/QnSdPa55jesJa.', NULL, NULL, 'active', 1, '2026-02-05 20:49:06', 1, '2026-01-21 14:19:06'),
(5, 6, NULL, 'Redgine Pinedes', 'Teacher II', 'CID', 3, 'teacher@deped.com', '$2y$10$nkw03ywO9gVfOBC7jfASBuNK7TPbvq2EuS6BRWq5YXaPmn9QyVGEa', NULL, NULL, 'active', 1, '2026-02-09 09:35:25', NULL, '2026-01-21 14:27:40'),
(6, 3, NULL, 'Paul Jeremy I. Aguja', 'Administrative Office V', 'OSDS', 10, 'aov@deped.com', '$2y$10$tzAnD5Ux6jaAUJC6oHbYa.7zjGAOBPqnqpQGrOu8UAcfFwrhzW.tq', NULL, NULL, 'active', 1, '2026-02-05 20:18:31', 1, '2026-01-22 06:16:01'),
(7, 4, NULL, 'Erma S. Valenzuela', 'CID - CHIEF', 'CID', 3, 'cidchief@deped.com', '$2y$10$5akgjur7Fkzx04XpJT6Be.DfJv0Bz.3q90nOeUhEncaPxOm9s6F8K', NULL, NULL, 'active', 1, '2026-02-09 09:35:15', 1, '2026-01-22 13:46:22'),
(8, 5, NULL, 'Frederick G. Byrd Jr.', 'SGOD - CHIEF', 'SGOD', 4, 'sgdochief@deped.com', '$2y$10$ZqdbL0T3LaLABt5GSplvZ.WRaLUv.DEim2QYm2.IsOLPmwZmMVfUu', NULL, NULL, 'active', 1, '2026-02-05 15:53:23', 1, '2026-01-22 13:48:29'),
(9, 6, NULL, 'Alexander Joerenz Escallente', 'ITO - 1', 'OSDS', 10, 'ito@deped.com', '$2y$10$1F5CMA6ZElJotpky9Mu48OHh.f4eUNkoURm1MB1prSCEJQj.26g02', NULL, NULL, 'active', 1, '2026-02-05 21:50:58', 1, '2026-01-22 13:50:02'),
(10, 6, NULL, 'Algen Loveres', 'PDO - I', 'SGOD', 4, 'pdo@deped.com', '$2y$10$hyA/cvQZ82kDzfcwJjGbkuuJh19XeY1xpEZT5XBPCdg9X.xJOSHHK', NULL, NULL, 'active', 1, '2026-02-05 15:57:14', 1, '2026-01-22 13:50:59'),
(11, 6, NULL, 'Cedrick Bacaresas', 'Accountant III', 'Records', 13, 'acct@deped.com', '$2y$10$6OTZvSd91tshfMbnL/b7ZOhdvZeb5HRrmaLbjdyzJqNZlJggfSWki', NULL, NULL, 'active', 1, '2026-01-26 10:33:56', 1, '2026-01-23 06:34:31'),
(12, 6, NULL, 'John Daniel P. Tec', 'Public Schools District Supervisor', 'CID', 3, 'psds@deped.com', '$2y$10$507mUh3jmm7b3p5xlw9V9u7ODHYTsHzxHrk.KUnS.p.4S/fmFRw.2', NULL, NULL, 'active', 1, '2026-01-26 13:32:55', 1, '2026-01-23 06:47:01'),
(13, 6, '122632', 'Eljohn S. Beleta', 'ICT Clerk', 'OSDS', NULL, 'ictclerk1@deped.com', '$2y$10$/8R7UZNdiez82smnMES6ge8XImVbBOyMIXbMQnthPyv7yXi.V7Tu6', NULL, NULL, 'active', 1, '2026-01-26 09:39:29', NULL, '2026-01-26 01:38:51'),
(14, 6, NULL, 'Gizelle Cabrejas', 'Legal Assistant 1', 'Legal', 16, 'lglasst@deped.com', '$2y$10$P/UM7d4PFnbbwSpXsA9sC.DgsAVEyQaiMn.zxUWGJrJA20woXqQMe', NULL, NULL, 'active', 1, '2026-01-26 11:38:24', 1, '2026-01-26 03:38:09'),
(15, 6, NULL, 'Abigail A. Olivenza', 'Administrative Assistant III', 'Personnel', 11, 'abigailaaiii@deped.com', '$2y$10$XkkeckM04VQ73u4xQuV1FOMg8l0JUGTpsxlma74/.leH3eefUj64G', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 00:40:31'),
(16, 6, '', 'Edwin Joseph B. De Peralta', 'Administrative Aide VI', 'Records', 37, 'edwinaavi@deped.com', '$2y$10$Xk3oOstf6Ohcn3qOFsaztOj9OZm.pz8U3mQtt8QHHJLSEGsO8KosW', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 01:00:43'),
(17, 6, '', 'Kristine Kate R. Aguilar', 'Administrative Aide VI', 'Cash', 38, 'kristineaavi@deped.com', '$2y$10$pD5bVI5Ydjv894TSdinlM.rRwc39.zFMz/DL9k/Exx4nuw8DV2cUu', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 01:26:12'),
(18, 6, '', 'Arcel F. Sopena', 'Administrative Officer  II', 'Procurement', 14, 'arcelaoii@deped.com', '$2y$10$V3r8d1Qq50xQv4afcQjo6ejDDnXA8MoFXo8f5Nt4VGMii4ZEU83qy', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 01:28:21'),
(19, 6, '', 'Mariano L. Abiad', 'LSB - Watchman', 'General Services', 15, 'marianoaoii@deped.com', '$2y$10$xk8aP3ZJt18LTSXSm/VM0Oobmeyv18.ZyZhZpe35UdbpXtevhVhwm', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 01:30:33'),
(20, 6, '', 'Jessa M.  Fiedalan', 'Administrative Officer  II', 'Accounting', 19, 'jessaaaii@deped.com', '$2y$10$qPydPOmVrXPgRuy0XdfB/eZBf/3VDEdOKe2YnowT7Bw/WPIP4kW9K', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 01:31:52'),
(21, 6, '', 'Baby Hazel Ann D. Perfas', 'Administrative Assistant III', 'Budget', 20, 'babyaaiii@deped.com', '$2y$10$s4vBBTWRkOBfVJh0BTzesuHXksBbkqh876kCGcGJvWGuZy9.4mPJS', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 01:33:10'),
(22, 6, '', 'Christoper  John C. Tabrilla', 'Administrative Aide VI', 'Property and Supply', 36, 'christoperaavi@deped.com', '$2y$10$QITj4J7DYZAjlSzPYMaP3uI.563UjPjtPszcmMhLXxpSsbRNXrYKK', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 01:36:58'),
(23, 6, NULL, 'Michael Angelo S. Moresca', 'Administrative Officer III', 'Personnel', 11, 'michaelaoiii@deped.com', '$2y$10$qU0SUZJIy2VxSOQJEyCrR.OmlXDn4Icw4.0Qzqqe0ULl14H80qkL2', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 01:55:21'),
(24, 6, NULL, 'Christian James C. Remoquillo', 'LSB - Clerk', 'Property and Supply', 36, 'christianlsb@deped.com', '$2y$10$ZDwqvDiohuSzLnsDZaa8N.i4j5gzNyVszU3TdgnSspk6Vrp/2cGUW', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 01:56:55'),
(25, 6, NULL, 'Micah Joy Alao', 'LSB - Clerk', 'Procurement', 14, 'micahlsb@deped.com', '$2y$10$gYKixbdzvfPKZzd3xDoGd.lDko5i6s56gKgTkbVT/shLqn.UvTYaW', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 01:58:04'),
(26, 6, NULL, 'Larry Bonoan', 'LSB - Clerk', 'Cash', 38, 'larrylsb@deped.com', '$2y$10$aPZFbdRkFE7k3Xq3.epJYOw9QOLITxW9CQBe.pOUAFKeXNq6Ic6Ku', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 01:59:11'),
(27, 6, NULL, 'Jun Jun Marinas', 'LSB - Clerk', 'Procurement', 14, 'junlsb@deped.com', '$2y$10$DP3J/HgaVnFIS/qtnUtYGut8.7UhEKvqdpZNHkzxlT..iH.DxLI3y', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 02:00:40'),
(28, 6, NULL, 'Jaypee Q. Jasa', 'Administrative Aide II', 'General Services', 15, 'jaypeeaaii@deped.com', '$2y$10$mGUvRk1GxKsz8O1VP2D8t.DSHAyZoS1PMx7O9m0hoSwCTezLZUhfe', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 02:08:15'),
(29, 6, NULL, 'Arlene Angeles', 'Administrative Aide VI', 'Accounting', 19, 'arleneaavi@deped.com', '$2y$10$rgb/p4/TF983nWYDo2xGHO5EszJ71EfxoFNwK2KVz/xzSaRHVXymC', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 02:10:32'),
(30, 6, NULL, 'Marvin Austria', 'LSB - Clerk', 'Budget', 20, 'marvinlsb@deped.com', '$2y$10$MpgZwGuLYS/VR0sc2D8ZN.9MjuPalS.foouOLmxk7SLU/gbayQ/AS', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 02:14:55'),
(31, 6, NULL, 'Shiela Mae Laude', 'Attorney III', 'Legal', 16, 'shielaatty@deped.com', '$2y$10$dVPoQahNRpYdBHlHGCrhleu8PH/VABr/NFn5b36SKH/0q699fuOiO', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 02:16:23'),
(32, 6, NULL, 'Rogelio Sapitula Jr.', 'LSB - Clerk', 'ICT', 17, 'jrlasb@deped.com', '$2y$10$QAVbqWYsrdDp23fk7hNxueg..b2rLditfyj1IUevkO/f8SyZpc7mu', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 02:17:28'),
(33, 6, NULL, 'Laurence Parto', 'Senior Education Program Specialist', 'SMME', 21, 'laurenceseps@deped.com', '$2y$10$oo2GpswIMZLHjnNBt5pmcewz.6WWEg2Cb4DEIEm4XwWeQKWandynC', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 02:50:44'),
(34, 6, NULL, 'Orimar Duab-dagandan', 'Senior Education Program Specialist', 'HRD', 22, 'orimarseps@deped.com', '$2y$10$l/SXDyOvii7e0nQJAk46Oe10X0JBn64pZU/oCt7ERDSUg3dniwL32', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 02:52:15'),
(35, 6, NULL, 'Mary Rose Aguilar', 'Senior Education Program Specialist', 'SMN', 23, 'maryseps@deped.com', '$2y$10$oOiEkF/0BTvkn3CsesF49eDb3bXwG8KWLZBbGeF4UTQcQ8lOKPmIi', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 02:53:31'),
(36, 6, NULL, 'Jenina Ambayec', 'Dentist II', 'SHN_DENTAL', 39, 'jeninadentist@deped.com', '$2y$10$piMIPmosdRVcMwyOJWi2pOPQS.WBkseThqRq0hfOhgxfOwoohreY6', NULL, NULL, 'active', 1, '2026-01-30 09:13:16', 1, '2026-01-29 02:58:09'),
(37, 6, '', 'Princess Leanna', 'LSB - Clerk', 'SMME', 21, 'cesslsb@deped.com', '$2y$10$iWltYtPm1pYr/BJ7gh.emOJC6B4E50/Q7QRi8aglQ.svf3wElQrLS', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 02:59:32'),
(38, 6, '', 'Shiela Manalo', 'LSB - Clerk', 'HRD', 22, 'shielalsb@deped.com', '$2y$10$oGRav0ybaKtd2ME7EvvhcOFpGJLZU0cMzzltl8RzcvJrHb12FEGm.', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 03:00:34'),
(39, 6, '', 'Jennesh Larena', 'Education Program Specialist', 'SMN', 23, 'jennesheps@deped.com', '$2y$10$G3Wt8lws42mUanfR35GBjuvDD9J/QkdUbjSDW5TqV.59UUxO/XGHK', NULL, NULL, 'active', 1, '2026-02-05 15:52:01', NULL, '2026-01-29 03:01:45'),
(40, 6, '', 'Ana Marie Mercado', 'LSB - Clerk', 'PR', 24, 'analsb@deped.com', '$2y$10$gVqSC/OUJZf61fgcEaFmfuuwflt2ErWK1eQGnkw5kFpTRZVJAt2RK', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 03:02:35'),
(41, 6, '', 'Jaimee Lee Aseoche', 'Nurse II', 'SHN_MEDICAL', 40, 'leenurse@deped.com', '$2y$10$4KWa9bZ7.W1w17ONGD6UQO5oXIRe7dLMh1l.i7P4cXivFI.qIiBgK', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 03:04:01'),
(42, 6, '', 'Marites Martinez', 'Education Program Supervisor - MAPEH', 'IM', 28, 'maritesepsm@deped.com', '$2y$10$UmvOxvhAHIfQn3s7n8yPo.obQlYi59uf77uAaPJn0B596cvxdu/6G', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 03:05:48'),
(43, 6, '', 'Carl Alora', 'Librarian II', 'LRM', 29, 'carllibrarian@deped.com', '$2y$10$2LHrX2pNh7iMlf33ykQu8.FzdG9AWWuahG3rAvMvhrywNMYi9MiRC', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 03:07:28'),
(44, 6, '', 'April Manlangit-Banaag', 'Education Program Specialist II ALS', 'ALS', 30, 'aprilepsii@deped.com', '$2y$10$hcmNCY6YEMDD8I6VI8H3dul462WQMRzWVS7rByYP1XoOLUzugYlfe', NULL, NULL, 'active', 1, NULL, NULL, '2026-01-29 03:09:01'),
(45, 6, '', 'Emelinda Amil', 'Public Schools District Supervisor', 'DIS', 35, 'emelpsds@deped.com', '$2y$10$DUF1.05oTCeqsKw44QVN/eeJHKyaz4dq7EbF3c9d2qXxePkminyb2', NULL, NULL, 'active', 1, '2026-02-05 20:53:31', NULL, '2026-01-29 03:10:36'),
(47, 6, NULL, 'Ernesto caberte', 'Education Program Supervisor - Filipino', 'IM', 28, 'ernestoepsf@deped.com', '$2y$10$RqxLCtmF4Xsfy8EQvXcNvu5LNcrlVDDQ7dvEgUsEXevg7VmgeXti2', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 03:14:52'),
(48, 6, NULL, 'Brianne Basilan', 'LSB - Clerk', 'LRM', 29, 'brilsb@deped.com', '$2y$10$ELBfV2zi1QeC0w6vfU.bOu4b0RjFhfUc1ZYX.f7oEM/jlTbQ8RDxm', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 03:16:16'),
(49, 6, NULL, 'Rowena June Mirondo', 'Education Program Specialist II ALS', 'ALS', 30, 'juneepsals@deped.com', '$2y$10$sCb8rjII6bbOUwY8Tom3R.pGAOIrTUSQ8jEVq4EnZOUTRVnOuA/Ii', NULL, NULL, 'active', 1, NULL, 1, '2026-01-29 03:17:29'),
(50, 6, NULL, 'Shirley Britos', 'Public Schools District Supervisor', 'DIS', 35, 'shirleypsds@deped.com', '$2y$10$LefWdH3nsrakpM3ZT5W3YOwBdxtNBuLH/dNKYB0tdO6YpuyUKz04.', NULL, NULL, 'active', 1, '2026-02-05 16:33:35', 1, '2026-01-29 03:18:38'),
(51, 3, NULL, 'Paul Jeremy I. Aguja', 'Administrative Officer V', 'OSDS', NULL, 'aov@deped.gov.ph', '$2y$10$i9CNq.Jmk./rwMFDdjmyLeCfp6xaYmHyczadTu5Ppo8p6ZJrwxQDm', NULL, NULL, 'active', 1, NULL, NULL, '2026-02-05 11:46:38'),
(54, 7, NULL, 'Phillip B. Gallendez', 'Schools Division Superintendent', NULL, NULL, 'sds@deped.com', '$2y$10$r6DRoqnHdPTn4DqAynpH4.3y9AMEHSwKlj002HFZZiV5N7IwzTOVi', NULL, NULL, 'active', 1, '2026-02-05 19:51:03', 1, '2026-02-05 11:50:38');

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
  `approving_time` datetime DEFAULT NULL,
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
  `requester_office_id` int(11) DEFAULT NULL,
  `requester_role_id` int(11) DEFAULT NULL,
  `assigned_approver_user_id` int(11) DEFAULT NULL COMMENT 'User ID of the assigned approver (can be OIC)',
  `date_filed` date DEFAULT NULL COMMENT 'Date the request was filed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authority_to_travel`
--

INSERT INTO `authority_to_travel` (`id`, `at_tracking_no`, `employee_name`, `employee_position`, `permanent_station`, `purpose_of_travel`, `host_of_activity`, `date_from`, `date_to`, `destination`, `fund_source`, `inclusive_dates`, `requesting_employee_name`, `request_date`, `recommending_authority_name`, `recommending_date`, `approving_authority_name`, `approval_date`, `approving_time`, `rejection_reason`, `travel_category`, `travel_scope`, `status`, `user_id`, `approved_by`, `created_at`, `updated_at`, `recommended_by`, `current_approver_role`, `routing_stage`, `requester_office`, `requester_office_id`, `requester_role_id`, `assigned_approver_user_id`, `date_filed`) VALUES
(1, 'AT-NATL-2026-000001', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'gutom', 'sdo', '2026-01-22', '2026-01-22', 'Batanes', 'local', NULL, 'Redgine Pinedes', '2026-01-22', 'Joe-Bren L. Consuelo', '2026-01-22', 'Joe-Bren L. Consuelo', '2026-01-22', '2026-01-22 00:00:00', NULL, 'official', 'national', 'approved', 5, 2, '2026-01-22 03:32:45', '2026-02-05 13:22:46', NULL, NULL, 'completed', NULL, NULL, NULL, NULL, '2026-01-22'),
(2, 'AT-PERS-2026-000001', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'ggs', '', '2026-01-22', '2026-01-22', 'Sta. Cruz, Laguna', '', NULL, 'Redgine Pinedes', '2026-01-22', 'Joe-Bren L. Consuelo', '2026-01-22', 'Joe-Bren L. Consuelo', '2026-01-22', '2026-01-22 00:00:00', NULL, 'personal', NULL, 'approved', 5, 2, '2026-01-22 03:34:32', '2026-02-05 13:22:46', NULL, NULL, 'completed', NULL, NULL, NULL, NULL, '2026-01-22'),
(3, 'AT-LOCAL-2026-000001', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'MEETING', 'REGION IV-A', '2026-01-22', '2026-01-22', 'Batanes', 'OWNED', NULL, 'Redgine Pinedes', '2026-01-22', 'Joe-Bren L. Consuelo', '2026-01-22', 'Joe-Bren L. Consuelo', '2026-01-22', '2026-01-22 00:00:00', NULL, 'official', 'local', 'approved', 5, 2, '2026-01-22 03:55:41', '2026-02-05 13:22:46', NULL, NULL, 'completed', NULL, NULL, NULL, NULL, '2026-01-22'),
(4, 'AT-PERS-2026-000002', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'Coffee', '', '2026-01-22', '2026-01-25', 'Festival', '', NULL, 'Redgine Pinedes', '2026-01-22', 'Paul Jeremy I. Aguja', '2026-01-22', 'Paul Jeremy I. Aguja', '2026-01-22', '2026-01-22 00:00:00', NULL, 'personal', NULL, 'approved', 5, 6, '2026-01-22 06:19:02', '2026-02-05 13:22:46', NULL, NULL, 'completed', NULL, NULL, NULL, NULL, '2026-01-22'),
(5, 'AT-NATL-2026-000002', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'coffee', 'REGION IV-A', '2026-01-22', '2026-01-22', 'Festival', 'OWNED', NULL, 'Redgine Pinedes', '2026-01-22', 'Paul Jeremy I. Aguja', '2026-01-22', 'Paul Jeremy I. Aguja', '2026-01-22', '2026-01-22 00:00:00', NULL, 'official', 'national', 'approved', 5, 6, '2026-01-22 06:58:03', '2026-02-05 13:22:46', NULL, NULL, 'completed', NULL, NULL, NULL, NULL, '2026-01-22'),
(6, 'AT-NATL-2026-000003', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'meeting', 'REGION IV-A', '2026-01-22', '2026-01-22', 'Batanes', 'local', NULL, 'Redgine Pinedes', '2026-01-22', 'Paul Jeremy I. Aguja', '2026-01-22', 'Paul Jeremy I. Aguja', '2026-01-22', '2026-01-22 00:00:00', NULL, 'official', 'national', 'approved', 5, 6, '2026-01-22 07:05:41', '2026-02-05 13:22:46', NULL, NULL, 'completed', NULL, NULL, NULL, NULL, '2026-01-22'),
(7, 'AT-2026-000004', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'ORIENTATION OF MILK', 'SDO SAN PEDRO CITY', '2026-01-23', '2026-01-29', 'Alaska', 'MOOE', NULL, 'Redgine Pinedes', '2026-01-22', NULL, NULL, NULL, NULL, NULL, NULL, 'official', 'local', 'pending', 5, NULL, '2026-01-22 13:52:28', '2026-01-26 07:27:11', NULL, 'ASDS', 'final', 'CID', 3, 4, NULL, '2026-01-22'),
(8, 'AT-2026-000005', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'ojt', 'SDO SAN PEDRO CITY', '2026-01-22', '2026-01-29', 'Alaska', 'MOOE', NULL, 'Redgine Pinedes', '2026-01-22', 'CID Chief', '2026-01-22', 'CID Chief', '2026-01-22', '2026-01-22 00:00:00', NULL, 'official', 'local', 'approved', 5, 7, '2026-01-22 14:08:01', '2026-02-05 13:22:46', 7, NULL, 'completed', 'CID', 3, 6, NULL, '2026-01-22'),
(9, 'AT-2026-000006', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'bvifuytfytdytd', 'REGION IV-A', '2026-01-22', '2026-01-23', 'Sta. Cruz, Laguna', 'MOOE', NULL, 'Redgine Pinedes', '2026-01-22', 'Erma S. Valenzuela, CID Chief', '2026-01-22', 'Erma S. Valenzuela, CID Chief', '2026-01-22', '2026-01-22 00:00:00', NULL, 'official', 'local', 'approved', 5, 7, '2026-01-22 14:24:26', '2026-02-05 13:22:46', 7, NULL, 'completed', 'CID', 3, 6, NULL, '2026-01-22'),
(10, 'AT-2026-000007', 'Alexander Joerenz Escallente', 'ITO - 1', 'SDO San Pedro City', 'MAGBABAWAS', 'SDO SAN PEDRO CITY', '2026-01-22', '2026-01-23', 'BAHAY', 'MOOE', NULL, 'Alexander Joerenz Escallente', '2026-01-22', 'Paul Jeremy I. Aguja, AO V', '2026-01-22', 'Paul Jeremy I. Aguja, AO V', '2026-01-22', '2026-01-22 00:00:00', NULL, 'official', 'local', 'approved', 9, 6, '2026-01-22 14:43:58', '2026-02-05 13:22:46', 6, NULL, 'completed', 'OSDS', 10, 6, NULL, '2026-01-22'),
(11, 'AT-2026-000008', 'Algen Loveres', 'PDO - I', 'SDO San Pedro City', 'VACATION', '', '2026-01-22', '2026-01-28', 'VIETNAM', '', NULL, 'Algen Loveres', '2026-01-22', 'Frederick G. Byrd Jr., SGOD Chief', '2026-01-22', 'Frederick G. Byrd Jr., SGOD Chief', '2026-01-22', '2026-01-22 00:00:00', NULL, 'personal', NULL, 'approved', 10, 8, '2026-01-22 14:46:49', '2026-02-05 13:22:46', 8, NULL, 'completed', 'SGOD', 4, 6, NULL, '2026-01-22'),
(12, 'AT-2026-000009', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'dfdfsfdsfs', 'REGION IV-A', '2026-01-23', '2026-01-31', 'Sta. Cruz, Laguna', 'local', NULL, 'Redgine Pinedes', '2026-01-23', 'Redgine Pinedes, CID Chief', '2026-01-23', 'Redgine Pinedes, CID Chief', '2026-01-23', '2026-01-23 00:00:00', NULL, 'official', 'local', 'approved', 5, 5, '2026-01-23 01:52:27', '2026-02-05 13:22:46', 5, NULL, 'completed', 'CID', 3, 6, NULL, '2026-01-23'),
(13, 'AT-2026-000010', 'Cedrick Bacaresas', '', 'SDO San Pedro City', 'hfhghhguyfuyvjyhv', 'REGION IV-A', '2026-01-23', '2026-01-25', 'Alaska', 'MOOE', NULL, 'Cedrick Bacaresas', '2026-01-23', NULL, NULL, NULL, NULL, NULL, 'no position included?\r\n', 'official', 'local', 'rejected', 11, 6, '2026-01-23 06:36:15', '2026-01-26 07:27:11', NULL, NULL, 'completed', 'Records', 13, 6, 6, '2026-01-23'),
(14, 'AT-2026-000011', 'Cedrick Bacaresas', 'Accountant III', 'SDO San Pedro City', 'werrwsfssfx', 'SDO SAN PEDRO CITY', '2026-01-23', '2026-01-24', 'Sta. Cruz, Laguna', 'MOOE', NULL, 'Cedrick Bacaresas', '2026-01-23', 'Paul Jeremy I. Aguja, AO V', '2026-01-26', 'Paul Jeremy I. Aguja, AO V', '2026-01-26', '2026-01-26 00:00:00', NULL, 'official', 'local', 'approved', 11, 6, '2026-01-23 06:40:26', '2026-02-05 13:22:46', 6, NULL, 'completed', 'Records', 13, 6, 6, '2026-01-23'),
(15, 'AT-2026-000012', 'John Daniel P. Tec', 'Public Schools District Supervisor', 'SDO San Pedro City', 'forgot something', 'SDO SAN PEDRO CITY', '2026-01-23', '2026-01-24', 'BAHAY', 'MOOE', NULL, 'John Daniel P. Tec', '2026-01-23', 'Erma S. Valenzuela, CID Chief', '2026-01-26', 'Erma S. Valenzuela, CID Chief', '2026-01-26', '2026-01-26 00:00:00', NULL, 'official', 'local', 'approved', 12, 7, '2026-01-23 06:48:06', '2026-02-05 13:22:46', 7, NULL, 'completed', 'CID', 3, 6, 5, '2026-01-23'),
(16, 'AT-2026-000013', 'eljohn', 'CLERK', 'SDO San Pedro City', 'meeting', '', '2026-01-26', '2026-01-27', 'Sta. Cruz, Laguna', '', NULL, 'eljohn', '2026-01-26', NULL, NULL, NULL, NULL, NULL, NULL, 'personal', NULL, 'pending', 13, NULL, '2026-01-26 02:33:08', '2026-01-26 02:33:08', NULL, 'OSDS_CHIEF', 'recommending', 'ICTO', NULL, 6, 6, '2026-01-26'),
(17, 'AT-2026-000014', 'eljohn', 'CLERK', 'SDO San Pedro City', 'kain', 'REGION IV-A', '2026-01-26', '2026-01-27', 'Alaska', 'MOOE', NULL, 'eljohn', '2026-01-26', NULL, NULL, NULL, NULL, NULL, NULL, 'official', 'national', 'pending', 13, NULL, '2026-01-26 02:34:56', '2026-01-26 02:34:56', NULL, 'OSDS_CHIEF', 'recommending', 'ICTO', NULL, 6, 6, '2026-01-26'),
(18, 'AT-2026-000015', 'eljohn', 'CLERK', 'SDO San Pedro City', 'rdgdfgdgdfgdgfg', '', '2026-01-26', '2026-01-27', 'Festival', '', NULL, 'eljohn', '2026-01-26', NULL, NULL, NULL, NULL, NULL, NULL, 'personal', NULL, 'pending', 13, NULL, '2026-01-26 03:16:20', '2026-01-26 07:27:11', NULL, 'OSDS_CHIEF', 'recommending', 'ICT', 17, 6, 6, '2026-01-26'),
(19, 'AT-2026-000016', 'eljohn', 'CLERK', 'SDO San Pedro City', 'werwrwwdfsf', '', '2026-01-26', '2026-01-28', 'Sta. Cruz, Laguna', '', NULL, 'eljohn', '2026-01-26', NULL, NULL, NULL, NULL, NULL, NULL, 'personal', NULL, 'pending', 13, NULL, '2026-01-26 03:17:16', '2026-01-26 03:17:16', NULL, 'OSDS_CHIEF', 'recommending', 'ICTO', NULL, 6, 6, '2026-01-26'),
(20, 'AT-2026-000017', 'Alexander Joerenz Escallente', 'ITO - 1', 'SDO San Pedro City', 'gagala', '', '2026-01-26', '2026-01-28', 'Sta. Cruz, Laguna', '', NULL, 'Alexander Joerenz Escallente', '2026-01-26', NULL, NULL, NULL, NULL, NULL, NULL, 'personal', NULL, 'pending', 9, NULL, '2026-01-26 03:18:47', '2026-01-26 07:27:11', NULL, 'OSDS_CHIEF', 'recommending', 'OSDS', 10, 6, 6, '2026-01-26'),
(21, 'AT-2026-000018', 'eljohn', 'ICT Clerk', 'SDO San Pedro City', 'gagalaaaaaa', '', '2026-01-26', '2026-01-27', 'Festival', '', NULL, 'eljohn', '2026-01-26', 'Paul Jeremy I. Aguja, AO V', '2026-01-26', 'Paul Jeremy I. Aguja, AO V', '2026-01-26', '2026-01-26 00:00:00', NULL, 'personal', NULL, 'approved', 13, 6, '2026-01-26 03:21:23', '2026-02-05 13:22:46', 6, NULL, 'completed', 'OSDS', 10, 6, 6, '2026-01-26'),
(22, 'AT-2026-000019', 'Gizelle Cabrejas', 'Legal Assistant 1', 'SDO San Pedro City', 'relaxing', '', '2026-01-26', '2026-01-29', 'batangas', '', NULL, 'Gizelle Cabrejas', '2026-01-26', NULL, NULL, NULL, NULL, NULL, NULL, 'personal', NULL, 'pending', 14, NULL, '2026-01-26 03:38:43', '2026-01-26 07:27:11', NULL, 'OSDS_CHIEF', 'recommending', 'Legal', 16, 6, 6, '2026-01-26'),
(23, 'AT-2026-000024', 'Jenina Ambayec', 'Dentist II', 'SDO San Pedro City', 'meeting', 'meeting', '2026-01-30', '2026-01-31', 'Sta. Cruz, Laguna', 'MOOE', NULL, 'Jenina Ambayec', '2026-01-30', NULL, NULL, NULL, NULL, NULL, NULL, 'official', 'local', 'pending', 36, NULL, '2026-01-30 01:16:49', '2026-01-30 01:16:49', NULL, 'SGOD_CHIEF', 'recommending', 'SHN_DENTAL', 39, 6, 6, '2026-01-30'),
(24, 'AT-2026-000025', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'Seminar', 'SDO - SPL', '2026-02-04', '2026-02-08', 'Cavite', 'Personal', NULL, 'Redgine Pinedes', '2026-02-04', 'CID Chief', '2026-02-05', 'Phillip B. Gallendez, SDS', '2026-02-05', '2026-02-05 21:24:38', NULL, 'official', 'local', 'approved', 5, 54, '2026-02-04 02:52:18', '2026-02-05 13:24:38', 7, NULL, 'completed', 'CID', 3, 6, 6, '2026-02-04'),
(25, 'AT-2026-000026', 'Jennesh Larena', 'Education Program Specialist', 'SDO San Pedro City', 'meeting', 'REGION IV-A', '2026-02-05', '2026-02-07', 'Sta. Cruz, Laguna', 'MOOE', NULL, 'Jennesh Larena', '2026-02-05', 'Frederick G. Byrd Jr., SGOD Chief', '2026-02-05', 'Frederick G. Byrd Jr., SGOD Chief', '2026-02-05', '2026-02-05 00:00:00', NULL, 'official', 'local', 'approved', 39, 8, '2026-02-05 07:53:01', '2026-02-05 13:22:46', 8, NULL, 'completed', 'SMN', 23, 6, 6, '2026-02-05'),
(26, 'AT-2026-000027', 'Jennesh Larena', 'Education Program Specialist', 'SDO San Pedro City', 'meeting', 'REGION IV-A', '2026-02-07', '2026-02-09', 'Alaska', 'MOOE', NULL, 'Jennesh Larena', '2026-02-05', 'Frederick G. Byrd Jr., SGOD Chief', '2026-02-05', 'Frederick G. Byrd Jr., SGOD Chief', '2026-02-05', '2026-02-05 00:00:00', NULL, 'official', 'national', 'approved', 39, 8, '2026-02-05 08:07:34', '2026-02-05 13:22:46', 8, NULL, 'completed', 'SMN', 23, 6, 6, '2026-02-05'),
(27, 'AT-2026-000028', 'Emelinda Amil', 'Public Schools District Supervisor', 'SDO San Pedro City', 'SEMINAR', 'CALABARZON', '2026-02-05', '2026-02-08', 'batangas', 'MOOE', NULL, 'Emelinda Amil', '2026-02-05', 'CID Chief', '2026-02-05', 'Phillip B. Gallendez, SDS', '2026-02-05', '2026-02-05 00:00:00', NULL, 'official', 'local', 'approved', 45, 54, '2026-02-05 12:54:06', '2026-02-05 13:22:46', 7, NULL, 'completed', 'DIS', 35, 6, 6, '2026-02-05'),
(28, 'AT-2026-000029', 'Redgine Pinedes', 'Teacher II', 'SDO San Pedro City', 'seminar', 'CALABARZON', '2026-02-05', '2026-02-12', 'antique', 'MOOE', NULL, 'Redgine Pinedes', '2026-02-05', 'Erma S. Valenzuela, CID Chief', '2026-02-05', 'Phillip B. Gallendez, SDS', '2026-02-05', '2026-02-05 21:38:04', NULL, 'official', 'local', 'approved', 5, 54, '2026-02-05 13:37:44', '2026-02-05 13:38:04', 7, NULL, 'completed', 'CID', 3, 6, 6, '2026-02-05'),
(29, 'AT-2026-000030', 'Paul Jeremy I. Aguja', 'Administrative Office V', 'SDO San Pedro City', 'vacation', '', '2026-02-05', '2026-02-28', 'mongolia', '', NULL, 'Paul Jeremy I. Aguja', '2026-02-05', NULL, NULL, 'Phillip B. Gallendez, SDS', '2026-02-05', '2026-02-05 21:54:14', NULL, 'personal', 'national', 'approved', 6, 54, '2026-02-05 13:53:38', '2026-02-05 13:54:14', NULL, NULL, 'completed', 'OSDS', 10, 3, NULL, '2026-02-05');

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
  `approving_time` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `user_id` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `assigned_approver_role_id` int(11) DEFAULT NULL COMMENT 'Role ID of the unit head approver',
  `assigned_approver_user_id` int(11) DEFAULT NULL COMMENT 'User ID of the assigned approver (can be OIC)',
  `requester_office` varchar(150) DEFAULT NULL COMMENT 'Office of the requester',
  `requester_role_id` int(11) DEFAULT NULL COMMENT 'Role ID of the requester',
  `date_filed` date DEFAULT NULL COMMENT 'Date the request was filed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locator_slips`
--

INSERT INTO `locator_slips` (`id`, `ls_control_no`, `employee_name`, `employee_position`, `employee_office`, `purpose_of_travel`, `travel_type`, `date_time`, `destination`, `requesting_employee_name`, `request_date`, `approver_name`, `approver_position`, `approval_date`, `approving_time`, `rejection_reason`, `status`, `user_id`, `approved_by`, `created_at`, `updated_at`, `assigned_approver_role_id`, `assigned_approver_user_id`, `requester_office`, `requester_role_id`, `date_filed`) VALUES
(1, 'LS-2026-000001', 'Redgine Pinedes', 'Teacher II', 'CID', 'Meeting For Orientation', 'official', '2026-01-21 15:29:00', 'Sta. Cruz, Laguna', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'ASDS', '2026-01-21', '2026-01-21 00:00:00', NULL, 'approved', 5, 2, '2026-01-21 14:29:23', '2026-02-05 11:47:17', NULL, NULL, 'CID', 6, '2026-01-21'),
(2, 'LS-2026-000002', 'Redgine Pinedes', 'Teacher II', 'CID', 'Meeting For Orientation', 'official', '2026-01-21 15:29:00', 'Sta. Cruz, Laguna', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'ASDS', '2026-01-21', '2026-01-21 00:00:00', NULL, 'approved', 5, 2, '2026-01-21 15:02:15', '2026-02-05 11:47:17', NULL, NULL, 'CID', 6, '2026-01-21'),
(3, 'LS-2026-000003', 'Redgine Pinedes', 'Teacher II', 'CID', 'Meeting For Orientation', 'official', '2026-01-21 15:29:00', 'Sta. Cruz, Laguna', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'ASDS', '2026-01-21', '2026-01-21 00:00:00', NULL, 'approved', 5, 2, '2026-01-21 15:02:30', '2026-02-05 11:47:17', NULL, NULL, 'CID', 6, '2026-01-21'),
(4, 'LS-2026-000004', 'Redgine Pinedes', 'Teacher II', 'CID', 'gagala', 'official_time', '2026-01-21 16:41:00', 'Batanes', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'ASDS', '2026-01-21', '2026-01-21 00:00:00', NULL, 'approved', 5, 2, '2026-01-21 15:44:51', '2026-02-05 11:47:17', NULL, NULL, 'CID', 6, '2026-01-21'),
(5, 'LS-2026-000005', 'Redgine Pinedes', 'Teacher II', 'CID', 'secret', 'official_business', '2026-01-21 16:44:00', 'Batanes', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'ASDS', '2026-01-21', '2026-01-21 00:00:00', NULL, 'approved', 5, 2, '2026-01-21 15:48:11', '2026-02-05 11:47:17', NULL, NULL, 'CID', 6, '2026-01-21'),
(6, 'LS-2026-000006', 'Redgine Pinedes', 'Teacher II', 'CID', 'sdoo', 'official_business', '2026-01-21 16:48:00', 'Sta. Cruz, Laguna', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'ASDS', '2026-01-21', '2026-01-21 00:00:00', NULL, 'approved', 5, 2, '2026-01-21 15:49:30', '2026-02-05 11:47:17', NULL, NULL, 'CID', 6, '2026-01-21'),
(7, 'LS-2026-000007', 'Redgine Pinedes', 'Teacher II', 'CID', 'NAGUGUTOM', 'official_business', '2026-01-21 16:56:00', 'SDO', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'Assistant Schools Division Superintendent', '2026-01-21', '2026-01-21 00:00:00', NULL, 'approved', 5, 2, '2026-01-21 15:56:25', '2026-02-05 11:47:17', NULL, NULL, 'CID', 6, '2026-01-21'),
(8, 'LS-2026-000008', 'Redgine Pinedes', 'Teacher II', 'CID', 'NAGUGUTOM', 'official_business', '2026-01-21 16:56:00', 'SDO', 'Redgine Pinedes', '2026-01-21', 'Joe-Bren L. Consuelo', 'Assistant Schools Division Superintendent', '2026-01-21', '2026-01-21 00:00:00', NULL, 'approved', 5, 2, '2026-01-21 15:57:32', '2026-02-05 11:47:17', NULL, NULL, 'CID', 6, '2026-01-21'),
(9, 'LS-2026-000009', 'Redgine Pinedes', 'Teacher II', 'CID', 'coffee\r\n', 'official_business', '2026-01-22 08:02:00', 'Sta. Cruz, Laguna', 'Redgine Pinedes', '2026-01-22', NULL, NULL, NULL, NULL, NULL, 'pending', 5, NULL, '2026-01-22 07:03:07', '2026-01-23 02:42:11', NULL, NULL, 'CID', 6, '2026-01-22'),
(10, 'LS-2026-000010', 'Algen Loveres', 'PDO - I', 'SGOD', 'FORGOT SOMETHING', 'official_business', '2026-01-22 10:04:00', 'BAHAY', 'Algen Loveres', '2026-01-22', 'Paul Jeremy I. Aguja', 'Administrative Officer V', '2026-01-22', '2026-01-22 00:00:00', NULL, 'approved', 10, 6, '2026-01-22 15:05:02', '2026-02-05 11:47:17', NULL, NULL, 'SGOD', 6, '2026-01-22'),
(11, 'LS-2026-000011', 'Algen Loveres', 'PDO - I', 'SGOD', 'may babalikan lang', 'official_time', '2026-01-22 10:00:00', 'BAHAY', 'Algen Loveres', '2026-01-22', 'Joe-Bren L. Consuelo', 'Assistant Schools Division Superintendent', '2026-01-22', '2026-01-22 00:00:00', NULL, 'approved', 10, 2, '2026-01-22 15:14:01', '2026-02-05 11:47:17', NULL, NULL, 'SGOD', 6, '2026-01-22'),
(12, 'LS-2026-000012', 'Redgine Pinedes', 'Teacher II', 'CID', 'shopping', 'official_business', '2026-01-23 03:44:00', 'moa', 'Redgine Pinedes', '2026-01-23', 'Erma S. Valenzuela', 'CID - CHIEF', '2026-01-23', '2026-01-23 00:00:00', NULL, 'approved', 5, 7, '2026-01-23 02:44:44', '2026-02-05 11:47:17', 4, 7, 'CID', 6, '2026-01-23'),
(13, 'LS-2026-000013', 'Emelinda Amil', 'Public Schools District Supervisor', 'DIS', 'PODCAST', 'official_business', '2026-01-30 14:00:00', 'Sta. Cruz, Laguna', 'Emelinda Amil', '2026-01-30', NULL, NULL, NULL, NULL, NULL, 'pending', 45, NULL, '2026-01-30 01:09:51', '2026-01-30 01:09:51', 3, 6, 'DIS', 6, '2026-01-30'),
(14, 'LS-2026-000014', 'Jenina Ambayec', 'Dentist II', 'SHN_DENTAL', 'seminar', 'official_business', '2026-01-30 22:00:00', 'city hall', 'Jenina Ambayec', '2026-01-30', 'Paul Jeremy I. Aguja', 'Administrative Officer V', '2026-02-05', '2026-02-05 20:42:04', NULL, 'approved', 36, 6, '2026-01-30 01:13:33', '2026-02-05 12:42:04', 3, 6, 'SHN_DENTAL', 6, '2026-01-30'),
(15, 'LS-2026-000015', 'Jenina Ambayec', 'Dentist II', 'SHN_DENTAL', 'forgot something\r\n', 'official_business', '2026-01-30 02:37:00', 'BAHAY', 'Jenina Ambayec', '2026-01-30', NULL, NULL, NULL, NULL, '', 'rejected', 36, 6, '2026-01-30 01:38:04', '2026-01-30 02:50:11', 3, 6, 'SHN_DENTAL', 6, '2026-01-30'),
(16, 'LS-2026-000016', 'Jenina Ambayec', 'Dentist II', 'SHN_DENTAL', 'coffee', 'official_business', '2026-01-30 03:05:00', 'Festival', 'Jenina Ambayec', '2026-01-30', NULL, NULL, NULL, NULL, NULL, 'pending', 36, NULL, '2026-01-30 02:05:32', '2026-01-30 02:05:32', 5, 8, 'SHN_DENTAL', 6, '2026-01-30'),
(17, 'LS-2026-000017', 'Redgine Pinedes', 'Teacher II', 'CID', 'Seminar', 'official_business', '2026-02-04 03:49:00', 'Cavite', 'Redgine Pinedes', '2026-02-04', NULL, NULL, NULL, NULL, NULL, 'pending', 5, NULL, '2026-02-04 02:49:36', '2026-02-04 02:49:36', 4, 7, 'CID', 6, '2026-02-04'),
(18, 'LS-2026-000018', 'Jennesh Larena', 'Education Program Specialist', 'SMN', 'meeting', 'official_business', '2026-02-06 17:00:00', 'Sta. Cruz, Laguna', 'Jennesh Larena', '2026-02-05', 'Algen Loveres', 'PDO - I', '2026-02-05', '2026-02-05 00:00:00', NULL, 'approved', 39, 10, '2026-02-05 08:01:13', '2026-02-05 11:47:17', 5, 10, 'SMN', 6, '2026-02-05'),
(19, 'LS-2026-000019', 'Jennesh Larena', 'Education Program Specialist', 'SMN', 'gg', 'official_time', '2026-02-08 09:11:00', 'Alaska', 'Jennesh Larena', '2026-02-05', NULL, NULL, NULL, NULL, NULL, 'pending', 39, NULL, '2026-02-05 08:11:47', '2026-02-05 08:11:47', 5, 10, 'SMN', 6, '2026-02-05'),
(20, 'LS-2026-000020', 'Jennesh Larena', 'Education Program Specialist', 'SMN', 'ggg', 'official_time', '2026-02-09 09:12:00', 'Sta. Cruz, Laguna', 'Jennesh Larena', '2026-02-05', 'Frederick G. Byrd Jr.', 'SGOD - CHIEF', '2026-02-05', '2026-02-05 00:00:00', NULL, 'approved', 39, 8, '2026-02-05 08:12:49', '2026-02-05 11:47:17', 5, 8, 'SMN', 6, '2026-02-05'),
(21, 'LS-2026-000021', 'Emelinda Amil', 'Public Schools District Supervisor', 'DIS', 'secret', 'official_business', '2026-02-05 22:40:00', 'Cavite', 'Emelinda Amil', '2026-02-05', 'Paul Jeremy I. Aguja', 'Administrative Officer V', '2026-02-05', '2026-02-05 20:19:12', NULL, 'approved', 45, 6, '2026-02-05 12:18:10', '2026-02-05 12:19:12', 3, 6, 'DIS', 6, '2026-02-05'),
(22, 'LS-2026-000022', 'Paul Jeremy I. Aguja', 'Administrative Office V', 'OSDS', 'forgot something', 'official_business', '2026-02-05 20:50:00', 'house', 'Paul Jeremy I. Aguja', '2026-02-05', 'Joe-Bren L. Consuelo', 'Assistant Schools Division Superintendent', '2026-02-05', '2026-02-05 20:57:12', NULL, 'approved', 6, 2, '2026-02-05 12:43:23', '2026-02-05 12:57:12', 2, 2, 'OSDS', 3, '2026-02-05'),
(23, 'LS-2026-000023', 'Paul Jeremy I. Aguja', 'Administrative Office V', 'OSDS', 'starbucks saglit', 'official_business', '2026-02-05 20:55:00', 'san antonio', 'Paul Jeremy I. Aguja', '2026-02-05', NULL, NULL, NULL, NULL, 'ORAS NG TRABAHO BEH, ANO KA DIYAN, KAKAPE KAPE NALANG?\r\n', 'rejected', 6, 2, '2026-02-05 12:47:51', '2026-02-05 12:56:26', 2, 2, 'OSDS', 3, '2026-02-05');

-- --------------------------------------------------------

--
-- Table structure for table `oic_delegations`
--

CREATE TABLE `oic_delegations` (
  `id` int(11) NOT NULL,
  `unit_head_user_id` int(11) NOT NULL COMMENT 'The unit head who is delegating',
  `unit_head_role_id` int(11) NOT NULL COMMENT 'Role ID of the unit head (cid_chief, sgod_chief, osds_chief, etc.)',
  `oic_user_id` int(11) NOT NULL COMMENT 'The user assigned as OIC',
  `start_date` date NOT NULL COMMENT 'Start date of OIC period',
  `end_date` date NOT NULL COMMENT 'End date of OIC period',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Whether this delegation is currently active',
  `created_by` int(11) DEFAULT NULL COMMENT 'User who created this delegation',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='OIC Delegation assignments for unit heads';

--
-- Dumping data for table `oic_delegations`
--

INSERT INTO `oic_delegations` (`id`, `unit_head_user_id`, `unit_head_role_id`, `oic_user_id`, `start_date`, `end_date`, `is_active`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 7, 4, 5, '2026-01-23', '2026-01-23', 0, 7, '2026-01-23 03:44:17', '2026-01-23 03:44:28'),
(2, 7, 4, 5, '2026-01-23', '2026-01-30', 0, 7, '2026-01-23 03:44:39', '2026-01-23 07:19:43'),
(3, 7, 4, 5, '2026-01-23', '2026-01-30', 0, 7, '2026-01-23 07:19:56', '2026-01-23 08:16:23'),
(4, 8, 5, 10, '2026-02-05', '2026-02-12', 0, 8, '2026-02-05 07:56:21', '2026-02-05 07:57:37'),
(5, 8, 5, 10, '2026-02-05', '2026-02-12', 0, 8, '2026-02-05 07:57:55', '2026-02-05 08:12:04'),
(6, 7, 4, 5, '2026-02-05', '2026-02-12', 0, 7, '2026-02-05 13:42:33', '2026-02-05 13:50:32'),
(7, 6, 3, 9, '2026-02-05', '2026-02-12', 1, 6, '2026-02-05 13:50:22', '2026-02-05 13:50:22'),
(8, 7, 4, 5, '2026-02-09', '2026-02-16', 0, 7, '2026-02-09 01:35:55', '2026-02-09 01:36:08');

-- --------------------------------------------------------

--
-- Table structure for table `sdo_offices`
--

CREATE TABLE `sdo_offices` (
  `id` int(11) NOT NULL,
  `office_code` varchar(50) NOT NULL COMMENT 'Short code for the office (e.g., ICT, CID)',
  `office_name` varchar(150) NOT NULL COMMENT 'Full display name',
  `office_type` enum('executive','division','section','unit') NOT NULL DEFAULT 'section',
  `parent_office_id` int(11) DEFAULT NULL COMMENT 'Parent office for hierarchical structure',
  `approver_role_id` int(11) DEFAULT NULL COMMENT 'Role ID of recommending authority for this office',
  `is_osds_unit` tinyint(1) DEFAULT 0 COMMENT 'Flag for OSDS units under AO V',
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Master table for all SDO offices and units';

--
-- Dumping data for table `sdo_offices`
--

INSERT INTO `sdo_offices` (`id`, `office_code`, `office_name`, `office_type`, `parent_office_id`, `approver_role_id`, `is_osds_unit`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'SDS', 'Office of the Schools Division Superintendent', 'executive', NULL, NULL, 0, 1, 1, '2026-01-26 07:27:11', '2026-01-26 07:27:11'),
(2, 'ASDS', 'Office of the Assistant Schools Division Superintendent', 'executive', NULL, NULL, 0, 2, 1, '2026-01-26 07:27:11', '2026-01-26 07:27:11'),
(3, 'CID', 'Curriculum Implementation Division', 'division', NULL, 4, 0, 10, 1, '2026-01-26 07:27:11', '2026-01-26 07:27:11'),
(4, 'SGOD', 'School Governance and Operations Division (Main Office)', 'division', NULL, 5, 0, 11, 1, '2026-01-26 07:27:11', '2026-01-27 05:21:37'),
(10, 'OSDS', 'Office of the Schools Division Superintendent Staff', 'unit', 1, 3, 1, 20, 1, '2026-01-26 07:27:11', '2026-01-26 07:27:11'),
(11, 'Personnel', 'Personnel', 'section', NULL, 3, 1, 21, 1, '2026-01-26 07:27:11', '2026-01-29 00:51:39'),
(13, 'Records and Supply', 'Records and Supply Section', 'section', NULL, 3, 1, 23, 0, '2026-01-26 07:27:11', '2026-01-29 00:48:05'),
(14, 'Procurement', 'Procurement', 'section', NULL, 3, 1, 24, 1, '2026-01-26 07:27:11', '2026-01-29 00:51:39'),
(15, 'General Services', 'General Services', 'section', NULL, 3, 1, 25, 1, '2026-01-26 07:27:11', '2026-01-29 00:51:39'),
(16, 'Legal', 'Legal', 'unit', NULL, 3, 1, 26, 1, '2026-01-26 07:27:11', '2026-01-29 00:51:39'),
(17, 'ICT', 'Information and Communication Technology', 'unit', NULL, 3, 1, 27, 1, '2026-01-26 07:27:11', '2026-01-29 00:51:39'),
(19, 'Accounting', 'Finance (Accounting)', 'section', NULL, 3, 1, 31, 1, '2026-01-26 07:27:11', '2026-01-29 01:14:31'),
(20, 'Budget', 'Finance (Budget)', 'section', NULL, 3, 1, 32, 1, '2026-01-26 07:27:11', '2026-01-29 01:14:31'),
(21, 'SMME', 'School Management Monitoring and Evaluation', 'unit', 4, 5, 0, 40, 1, '2026-01-27 05:21:37', '2026-01-27 05:21:37'),
(22, 'HRD', 'Human Resource Development', 'unit', 4, 5, 0, 41, 1, '2026-01-27 05:21:37', '2026-01-27 05:21:37'),
(23, 'SMN', 'Social Mobilization and Networking', 'unit', 4, 5, 0, 42, 1, '2026-01-27 05:21:37', '2026-01-27 05:21:37'),
(24, 'PR', 'Planning and Research', 'unit', 4, 5, 0, 43, 1, '2026-01-27 05:21:37', '2026-01-27 05:21:37'),
(25, 'DRRM', 'Disaster Risk Reduction and Management', 'unit', 4, 5, 0, 44, 1, '2026-01-27 05:21:37', '2026-01-27 05:21:37'),
(26, 'EF', 'Education Facilities', 'unit', 4, 5, 0, 45, 1, '2026-01-27 05:21:37', '2026-01-27 05:21:37'),
(28, 'IM', 'Instructional Management', 'unit', 3, 4, 0, 50, 1, '2026-01-29 00:21:05', '2026-01-29 00:21:05'),
(29, 'LRM', 'Learning Resource Management', 'unit', 3, 4, 0, 51, 1, '2026-01-29 00:21:05', '2026-01-29 00:21:05'),
(30, 'ALS', 'Alternative Learning System', 'unit', 3, 4, 0, 52, 1, '2026-01-29 00:21:05', '2026-01-29 00:21:05'),
(35, 'DIS', 'District Instructional Supervision', 'unit', 3, 4, 0, 53, 1, '2026-01-29 00:24:08', '2026-01-29 00:24:08'),
(36, 'Property and Supply', 'Property and Supply', 'section', 1, 3, 1, 22, 1, '2026-01-29 00:48:05', '2026-01-29 00:51:39'),
(37, 'Records', 'Records', 'section', 1, 3, 1, 23, 1, '2026-01-29 00:48:05', '2026-01-29 00:51:39'),
(38, 'Cash', 'Cash', 'section', 1, 3, 1, 28, 1, '2026-01-29 01:14:31', '2026-01-29 01:14:31'),
(39, 'SHN_DENTAL', 'School Health and Nutrition (Dental)', 'section', 4, 5, 0, 47, 1, '2026-01-29 01:15:24', '2026-01-29 01:22:31'),
(40, 'SHN_MEDICAL', 'School Health and Nutrition (Medical)', 'section', 4, 5, 0, 48, 1, '2026-01-29 01:15:24', '2026-01-29 01:22:31');

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
(53, '0778785a19b0f4408da7c904c7bb369ee9439de9e0263dd022dd9708410955cc', 6, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 12:43:03', '2026-01-23 01:53:49'),
(54, '519a910bdc94b9d6e7d83bb6eb27f1db0c031d4524b1fd404be90f0737be0802', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 12:57:24', '2026-01-23 01:57:31'),
(55, '9c15b3631e758113322ba0d0bf8d7b5e347b090d8c80d876055fc6ac21adaa6c', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 12:52:23', '2026-01-23 02:43:34'),
(56, '3117c4e0c559a9d3329601ebb5304ed3f975608c60400d2e08ed3971e739665a', 7, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 12:44:39', '2026-01-23 03:36:39'),
(57, '1d5858e65dcf900da79e532b99783ea0ed78b1bce36a7ed4ed36bb4a4c43d23a', 8, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 12:51:28', '2026-01-23 03:51:28'),
(59, '3498ef382e378a845b9ab090b7731e646e0530e140732a05e275f6050e74a169', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 14:23:55', '2026-01-23 05:15:31'),
(60, '5ac0a8fb8908ca4c70ec5138f8e924ad09580a050780f1abb54386e825ef6aa9', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 14:17:39', '2026-01-23 05:16:42'),
(61, 'df4c2f3cb51b233b176658975a9ab6c150a3f8e661ed429eac4f6e6206252aaa', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 14:30:27', '2026-01-23 05:16:58'),
(62, '66ec50aef3c414b4e57c2b2c142d269e8e3578879e6f904c1e8c1bfbf96609f3', 8, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 14:17:29', '2026-01-23 05:17:05'),
(63, '81f8214b787404a7b88039d3f6637780c16ee2e056f741fe4d3156fdf7ba83da', 7, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 14:26:06', '2026-01-23 05:17:15'),
(64, '98e0a1582038f0e298062256b48b15089a3d985edd6b57c67f9726e9dba8504c', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 17:17:53', '2026-01-23 06:29:33'),
(66, '36e1dc4a6716f33c5fbb081bc7e966f5fbcd95edfb82b211c241ba900d2233b3', 6, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 15:45:03', '2026-01-23 06:37:03'),
(69, 'c71ed13d0e2113af457332c8c7126731b6564ad52952fc04be16be3f20a20dd2', 12, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-23 15:48:06', '2026-01-23 06:47:22'),
(70, '441fff3a65d4777bc880253ce88bbe7d57f677689539317c1dd01646e1128a0a', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-26 12:38:09', '2026-01-26 01:26:01'),
(71, '976d91abf381f1eca22bb2ac3705f25f56ddc6da9cf7e4485404b763903b45d5', 7, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-26 11:14:53', '2026-01-26 01:26:08'),
(72, 'f5117dea6b7806996801879ae9860964419db8da017c51b795f7126d7284d384', 8, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-26 10:36:06', '2026-01-26 01:26:26'),
(73, 'b738cbb159d2c5bed90b4bec02005359cb140c07643138688e103d99f3ff16f5', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-26 10:37:16', '2026-01-26 01:26:36'),
(75, '02d4db1051b6f47d2fa2a74f15012881811799e338612c95213f36aa25906a3d', 13, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-26 12:39:03', '2026-01-26 01:39:29'),
(78, 'b00ec00a57482853c009e247f205f6ae3c7edfd6398cdf0823798648b6c2422b', 6, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-26 12:38:57', '2026-01-26 02:37:32'),
(79, 'a74fbbee2bf7c0022a2832f3bd926b82fab724412613238937a0db8355e094b5', 8, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-26 12:13:17', '2026-01-26 03:13:05'),
(80, 'f067583c0999be67a7413bd06392292eba1c9741d9efbb84555610a940255196', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-26 12:18:26', '2026-01-26 03:13:17'),
(82, 'fde875e6e427a861c52ca499e11309d4853573bdf0d549490cc9b107ba88957d', 14, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-26 12:38:43', '2026-01-26 03:38:24'),
(83, 'a9919e1a5af7bc2c6cce4e83b24f16899439cf1061a92842dcd4ee40c4ca8b4e', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-26 14:32:55', '2026-01-26 05:32:34'),
(87, '2a09a2188f6e78908931877006859619ec2f961abe31490e9e9be9cb7d7ddf05', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-26 16:48:27', '2026-01-26 06:11:50'),
(88, 'd4b5de435105ddca355a1919aca2b43ebfc1c4abeb95e256ea7af40035a2068a', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-27 09:38:33', '2026-01-27 00:26:14'),
(89, 'bbc99f55129ce0688c875f3e900e2ec135285de0c779606682fdef28a359b59a', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-27 09:38:33', '2026-01-27 00:38:33'),
(90, 'd27091b67cd313a3788977aedfbb56f45be4a2f58d0f08740fc2f276beda1ef8', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-27 11:45:12', '2026-01-27 01:48:36'),
(91, 'cdd71397edaa78587d8aa156307b8bb4cc78b16592ceb116e748d02af87d4295', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-27 11:44:18', '2026-01-27 01:58:31'),
(94, 'af95c021a237a785da2ec090cd7199c61e93c9287881a8ad191bd2effbe43505', 6, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-27 11:46:16', '2026-01-27 02:46:05'),
(95, '2d6a86876915441a019d9af4344e19e682ddc79fb5471d6b3c4cd4b53e3e4afa', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-27 16:03:16', '2026-01-27 05:23:49'),
(96, '733154e5e591e2a22d44601eb0d5b692c7d7e8c7a2c02616c3bed8405b8bbb82', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-27 17:34:09', '2026-01-27 08:15:49'),
(98, 'e8994fa3bbb6bb7c71268619b12d6abef7358d5a64bfdca4fe7a546244da1040', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-27 17:39:42', '2026-01-27 08:34:40'),
(101, 'b384211a56ea99bd9dcb1e9da2cab17899005f77b7241a3859e53e6dcc93d98b', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-28 11:04:26', '2026-01-28 01:57:35'),
(102, '8e12752c0d7fee3c657a72177f53141f8098e37699cac371e0bd342613b6784e', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-28 11:04:28', '2026-01-28 02:04:26'),
(103, 'a4a9f59fb2d1b12ddf33e454bbe20b28ada9af3f38465a9a1daadfd1d2c4cf8a', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-29 12:18:38', '2026-01-29 00:14:21'),
(104, '0342f391ed7d3e2f890c61a1a58d6db13bc3302ce12b412fc3ff7cf1e206eaab', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-29 15:15:55', '2026-01-29 04:33:17'),
(105, '059fc11c193c01d08a5f075018072d454f605eea3f561cf43c4213d946e8280f', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-29 13:36:21', '2026-01-29 04:36:13'),
(106, 'ea0c18a4115cb45bbe25d141c2a6839476d8f8059c7fb65b43fca55c6b14be8b', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-29 16:53:40', '2026-01-29 07:53:29'),
(107, 'd323159cb7c24091bfafa04fd0f050295a494048184fc8bba5ee3852a5de07fe', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-29 17:33:38', '2026-01-29 07:53:40'),
(108, 'ca82b899cca35aff350551427c678fe382f699b0360391d875f4c3866de71529', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-30 12:12:59', '2026-01-30 00:34:28'),
(113, '6b254b54bbf73ee14cd41296ecb42766e1e7ec937f01ebba0efc0178fe7d3599', 8, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-30 11:50:22', '2026-01-30 01:14:17'),
(114, '92a7de5f1ebae348ac1696033688d1a5b77047f5ef87c89d5c90113827de505b', 6, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-30 11:50:12', '2026-01-30 01:14:33'),
(116, 'b4a91bbb0fef1219b5cc47ca8cad67e31ab82fbff67365a91a8d396165cc6f09', 7, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-30 11:50:32', '2026-01-30 02:50:32'),
(119, 'de125db564a9596fdf2a0d56f45b07b8c2b7698773489be15de8990e761cbbac', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-02 12:40:38', '2026-02-02 02:44:07'),
(121, 'e57b65159972df892fba1e6004ceb9d0612723bc6033232490fff3474d80f693', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-02 14:49:07', '2026-02-02 04:34:58'),
(122, '85159384cbcd7e99807f73586167f9c5262e21ce9212876f6a25c4aa671537ac', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-02 17:28:04', '2026-02-02 08:02:18'),
(123, '077df34286653cc896c3535deb8f3819d23fc155305ce5708db7e0bf79d24773', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-02 17:28:00', '2026-02-02 08:02:24'),
(124, '314133dd6a29168ce925944d3e422749e5d1f00855a8e18ba83f9bb40721943c', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-03 09:41:52', '2026-02-03 00:40:28'),
(125, '28954a99d25b85fcaaf876fd51ad8e5587e32813dc2f6494137aff044dae1dd6', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-03 09:41:54', '2026-02-03 00:41:52'),
(126, 'ea17610ccf112673f24a1b521e2ec1e19235b51bfb149c66b383418620797228', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-03 12:27:58', '2026-02-03 03:27:58'),
(127, '086705c64d4c68a1528a213c4fcac4b70c63b629175204b3282872262c886004', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-03 14:30:03', '2026-02-03 05:15:24'),
(131, '014a76d25a27c3b1fac6167db458ab197846b9ac2c62e4e51136a0c09208eee2', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-03 15:32:07', '2026-02-03 06:24:36'),
(133, 'd18a8f8551d76e9542bef75f58cad6f9b27d32ac615bb228dbafacfb7e560b22', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-03 15:52:42', '2026-02-03 06:28:07'),
(134, 'cfea826d6ddc0fbe68420033e424b72c31ad1eccd80e69db492e990c6245d246', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-03 15:33:34', '2026-02-03 06:32:07'),
(135, '9b8c2ba48905f7d2c6ed116a48132b52616b049982c1ffc1e2e16a2f50c87872', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-03 15:50:27', '2026-02-03 06:34:21'),
(136, 'f94318fa6f899478aeebc95b4566edcf2ccfd801cf74517f2683d63e5c9fbc04', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-04 09:16:42', '2026-02-04 00:07:40'),
(137, '001a48f60c585f5077bab31c0cbcc285a5aaac4ffd658c55c3df4846e3f37c67', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-04 09:15:37', '2026-02-04 00:15:37'),
(138, '6f1b4f2e2918731b33174fa8e585a9b68989f6aa1345d39651ab8654529743cc', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-04 11:05:17', '2026-02-04 01:53:54'),
(143, '8cb3fba2e8b719da9cec42a2550d3f6765e63290a2435e866eea19d7ed2547b2', 5, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-04 11:57:27', '2026-02-04 02:52:31'),
(144, 'a319a330b11954496aecce4a000267e75e232dd3d78c4961bd2a320de7d67f70', 7, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-04 11:56:04', '2026-02-04 02:54:52'),
(145, '461f095b129e64e910456225150148c0ede0425283d684e40c38d518760bab52', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-04 13:10:54', '2026-02-04 04:10:54'),
(146, 'e161c75e4e6287b3b636063eeecf36838e44f7b6e753d90c809052108bfcb8ca', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-04 15:38:20', '2026-02-04 06:38:18'),
(147, 'd6dc0649918197c93787affce0c625308edf95c15f2fc5f4212eeb21374a91c5', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-04 22:36:06', '2026-02-04 13:36:05'),
(148, '205195d093297681db4186f93b772dfdebe0d5ddd8d33ca763bb6845ec2fa5b2', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-04 22:51:21', '2026-02-04 13:43:21'),
(150, '06571468d9e3cdd5662d73c8117e378b4b7f708909e9e9373fe08461e8c4c952', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-05 11:46:07', '2026-02-05 01:55:50'),
(151, '145a4cb002319a634edbec7506419ccbc4fc0191149746e19ecc425da1ec00ac', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-05 17:53:22', '2026-02-05 07:50:08'),
(154, '50f673e808764620ea9d632d93501cccd7f74e244689a6f2f8cbcd23a8941a89', 8, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-05 17:15:48', '2026-02-05 07:53:23'),
(157, 'd609a0328167ce6c1aeaffae8836c0f51c59d6e59fe70dd7289f54248ba3e51e', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-05 17:20:23', '2026-02-05 08:17:51'),
(158, '347a142b8c5369b4345294e2bf77ac9264a83a22a72cc30edcc398ae261e7309', 50, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-05 17:33:46', '2026-02-05 08:33:35'),
(159, '31a769117490ecaee19c5f965dec1a39b41dc7a9de0b55bdb38abd68ff671401', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-05 20:51:03', '2026-02-05 11:49:31'),
(160, '6839c31d9b20a2a63c7314437f603ccff46ca66ddd70670b6bc9f77d4277c1d6', 54, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-06 05:54:14', '2026-02-05 11:51:03'),
(161, '720d4a1895a9242f3c2293d3b8918e8b27e9c5842a334a9c579076c6e7160f9b', 7, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-06 05:50:32', '2026-02-05 12:13:36'),
(163, 'f5f7e36615ace90c1689b2fb64dccd12579b9870a26cf7ebeaf9a33b0a671e04', 6, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-06 05:54:25', '2026-02-05 12:18:31'),
(164, '21f9b8e09a00853965b54103eb38262d57509df2663ac4813da750a0b20e40fb', 2, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-06 04:57:12', '2026-02-05 12:49:06'),
(165, 'edfe8cd56c66004884c32fbb1782376f1f303523adc83d8ee8825be88fb9a9f8', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-06 05:50:51', '2026-02-05 12:51:30'),
(168, '421a3cb2455db6fae7720d06b4a4eb25ff7332c4d4aea9b0ec116823aa227dbd', 9, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-06 05:52:20', '2026-02-05 13:50:58'),
(172, '53677eb3f106b38235fd6779903d7e45e3093397c6aa1eda28c8051599a909d1', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-02-09 19:02:35', '2026-02-09 03:00:30');

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
(1, 'LS', 2026, 23),
(2, 'AT-LOCAL', 2026, 1),
(3, 'AT-NATL', 2026, 3),
(4, 'AT-PERS', 2026, 2),
(5, 'AT', 2026, 30);

-- --------------------------------------------------------

--
-- Table structure for table `unit_routing_config`
--

CREATE TABLE `unit_routing_config` (
  `id` int(11) NOT NULL,
  `unit_name` varchar(100) NOT NULL COMMENT 'Unit/Section name matching employee_office in admin_users',
  `office_id` int(11) DEFAULT NULL,
  `unit_display_name` varchar(150) NOT NULL COMMENT 'Full display name for UI',
  `approver_role_id` int(11) NOT NULL COMMENT 'Role ID of recommending authority',
  `travel_scope` enum('all','local','international') DEFAULT 'all' COMMENT 'Travel scope this routing applies to',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0 COMMENT 'Display order in admin panel',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Maps units/sections to their approving authority for AT routing';

--
-- Dumping data for table `unit_routing_config`
--

INSERT INTO `unit_routing_config` (`id`, `unit_name`, `office_id`, `unit_display_name`, `approver_role_id`, `travel_scope`, `is_active`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Personnel', 11, 'Personnel Section', 3, 'all', 1, 1, '2026-01-26 06:18:40', '2026-02-03 05:44:14'),
(4, 'Records and Supply', 13, 'Records and Supply Section', 3, 'all', 1, 4, '2026-01-26 06:18:40', '2026-01-26 07:32:19'),
(7, 'Procurement', 14, 'Procurement Section', 3, 'all', 1, 7, '2026-01-26 06:18:40', '2026-01-26 07:27:11'),
(8, 'General Services', 15, 'General Services Section', 3, 'all', 1, 8, '2026-01-26 06:18:40', '2026-01-26 07:27:11'),
(9, 'Legal', 16, 'Legal Unit', 3, 'all', 1, 9, '2026-01-26 06:18:40', '2026-01-26 07:27:11'),
(10, 'ICT', 17, 'Information and Communication Technology Unit', 3, 'all', 1, 10, '2026-01-26 06:18:40', '2026-01-26 07:27:11'),
(13, 'Accounting', 19, 'Finance (Accounting)', 3, 'all', 1, 31, '2026-01-26 06:18:40', '2026-01-29 01:22:56'),
(14, 'Budget', 20, 'Finance (Budget)', 3, 'all', 1, 32, '2026-01-26 06:18:40', '2026-01-29 01:22:56'),
(17, 'OSDS', 10, 'Office of the Schools Division Superintendent', 3, 'all', 1, 17, '2026-01-26 06:18:40', '2026-01-26 07:27:11'),
(18, 'CID', 3, 'Curriculum Implementation Division', 4, 'all', 1, 18, '2026-01-26 06:18:40', '2026-01-26 07:27:11'),
(19, 'SGOD', 4, 'School Governance and Operations Division', 5, 'all', 1, 19, '2026-01-26 06:18:40', '2026-01-26 07:27:11'),
(20, 'SMME', 21, 'School Management Monitoring and Evaluation', 5, 'all', 1, 40, '2026-01-27 05:21:37', '2026-01-27 05:21:37'),
(21, 'HRD', 22, 'Human Resource Development', 5, 'all', 1, 41, '2026-01-27 05:21:37', '2026-01-27 05:21:37'),
(22, 'SMN', 23, 'Social Mobilization and Networking', 5, 'all', 1, 42, '2026-01-27 05:21:37', '2026-01-27 05:21:37'),
(23, 'PR', 24, 'Planning and Research', 5, 'all', 1, 43, '2026-01-27 05:21:37', '2026-01-27 05:21:37'),
(24, 'DRRM', 25, 'Disaster Risk Reduction and Management', 5, 'all', 1, 44, '2026-01-27 05:21:37', '2026-01-27 05:21:37'),
(25, 'EF', 26, 'Education Facilities', 5, 'all', 1, 45, '2026-01-27 05:21:37', '2026-01-27 05:21:37'),
(27, 'ALS', 30, 'Alternative Learning System', 4, 'all', 1, 52, '2026-01-29 00:21:05', '2026-01-29 00:21:05'),
(28, 'IM', 28, 'Instructional Management', 4, 'all', 1, 50, '2026-01-29 00:21:05', '2026-01-29 00:21:05'),
(29, 'LRM', 29, 'Learning Resource Management', 4, 'all', 1, 51, '2026-01-29 00:21:05', '2026-01-29 00:21:05'),
(30, 'DIS', 35, 'District Instructional Supervision', 4, 'all', 1, 53, '2026-01-29 00:24:08', '2026-01-29 00:24:08'),
(31, 'Cash', 38, 'Cash', 3, 'all', 1, 28, '2026-01-29 01:14:31', '2026-01-29 01:14:31'),
(32, 'SHN_DENTAL', 39, 'School Health and Nutrition (Dental)', 5, 'all', 1, 47, '2026-01-29 01:15:24', '2026-01-29 01:15:24'),
(33, 'SHN_MEDICAL', 40, 'School Health and Nutrition (Medical)', 5, 'all', 1, 48, '2026-01-29 01:15:24', '2026-01-29 01:15:24'),
(37, 'Property and Supply', NULL, 'Property and Supply Unit', 3, 'all', 1, 3, '2026-02-05 11:48:00', '2026-02-05 11:48:00'),
(38, 'Records', NULL, 'Records Management Unit', 3, 'all', 1, 4, '2026-02-05 11:48:00', '2026-02-05 11:48:00');

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
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_user_office_id` (`office_id`);

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
  ADD KEY `idx_routing` (`current_approver_role`,`routing_stage`),
  ADD KEY `idx_assigned_approver_user` (`assigned_approver_user_id`),
  ADD KEY `idx_date_filed` (`date_filed`),
  ADD KEY `idx_at_office_id` (`requester_office_id`);

--
-- Indexes for table `locator_slips`
--
ALTER TABLE `locator_slips`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ls_control_no` (`ls_control_no`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user` (`user_id`),
  ADD KEY `idx_created` (`created_at`),
  ADD KEY `idx_assigned_approver` (`assigned_approver_role_id`,`assigned_approver_user_id`),
  ADD KEY `idx_requester_office` (`requester_office`),
  ADD KEY `idx_date_filed` (`date_filed`),
  ADD KEY `idx_approval_date` (`approval_date`);

--
-- Indexes for table `oic_delegations`
--
ALTER TABLE `oic_delegations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_unit_head` (`unit_head_user_id`,`unit_head_role_id`),
  ADD KEY `idx_oic_user` (`oic_user_id`),
  ADD KEY `idx_dates` (`start_date`,`end_date`,`is_active`),
  ADD KEY `idx_active` (`is_active`,`start_date`,`end_date`);

--
-- Indexes for table `sdo_offices`
--
ALTER TABLE `sdo_offices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `office_code` (`office_code`),
  ADD KEY `parent_office_id` (`parent_office_id`),
  ADD KEY `idx_code` (`office_code`),
  ADD KEY `idx_type` (`office_type`),
  ADD KEY `idx_osds` (`is_osds_unit`),
  ADD KEY `idx_approver` (`approver_role_id`),
  ADD KEY `idx_active` (`is_active`);

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
-- Indexes for table `unit_routing_config`
--
ALTER TABLE `unit_routing_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unit_name` (`unit_name`),
  ADD KEY `idx_unit_name` (`unit_name`),
  ADD KEY `idx_approver` (`approver_role_id`),
  ADD KEY `idx_active` (`is_active`),
  ADD KEY `fk_routing_office` (`office_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=534;

--
-- AUTO_INCREMENT for table `admin_roles`
--
ALTER TABLE `admin_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=55;

--
-- AUTO_INCREMENT for table `authority_to_travel`
--
ALTER TABLE `authority_to_travel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `locator_slips`
--
ALTER TABLE `locator_slips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `oic_delegations`
--
ALTER TABLE `oic_delegations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `sdo_offices`
--
ALTER TABLE `sdo_offices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `session_tokens`
--
ALTER TABLE `session_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=173;

--
-- AUTO_INCREMENT for table `tracking_sequences`
--
ALTER TABLE `tracking_sequences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `unit_routing_config`
--
ALTER TABLE `unit_routing_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

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
  ADD CONSTRAINT `admin_users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`),
  ADD CONSTRAINT `fk_user_office` FOREIGN KEY (`office_id`) REFERENCES `sdo_offices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `authority_to_travel`
--
ALTER TABLE `authority_to_travel`
  ADD CONSTRAINT `authority_to_travel_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`),
  ADD CONSTRAINT `authority_to_travel_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `admin_users` (`id`),
  ADD CONSTRAINT `fk_at_office` FOREIGN KEY (`requester_office_id`) REFERENCES `sdo_offices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `locator_slips`
--
ALTER TABLE `locator_slips`
  ADD CONSTRAINT `locator_slips_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`),
  ADD CONSTRAINT `locator_slips_ibfk_2` FOREIGN KEY (`approved_by`) REFERENCES `admin_users` (`id`);

--
-- Constraints for table `sdo_offices`
--
ALTER TABLE `sdo_offices`
  ADD CONSTRAINT `sdo_offices_ibfk_1` FOREIGN KEY (`parent_office_id`) REFERENCES `sdo_offices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sdo_offices_ibfk_2` FOREIGN KEY (`approver_role_id`) REFERENCES `admin_roles` (`id`);

--
-- Constraints for table `session_tokens`
--
ALTER TABLE `session_tokens`
  ADD CONSTRAINT `session_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `unit_routing_config`
--
ALTER TABLE `unit_routing_config`
  ADD CONSTRAINT `fk_routing_office` FOREIGN KEY (`office_id`) REFERENCES `sdo_offices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `unit_routing_config_ibfk_1` FOREIGN KEY (`approver_role_id`) REFERENCES `admin_roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
