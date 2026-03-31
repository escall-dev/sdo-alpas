-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 21, 2026 at 03:16 PM
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
(7, 1, 'login', 'auth', NULL, 'User logged in', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-01-21 14:12:48');

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
(1, 'superadmin', 'Full system access', '{\"all\": true}', 1, '2026-01-21 13:54:36'),
(2, 'asds', 'Assistant Schools Division Superintendent - Primary Approver', '{\"requests.view\": true, \"requests.approve\": true, \"logs.view\": true, \"analytics.view\": true}', 1, '2026-01-21 13:54:36'),
(3, 'aov', 'Administrative Officer V - Approver when ASDS unavailable', '{\"requests.view\": true, \"requests.approve\": true, \"logs.view\": true, \"analytics.view\": true}', 1, '2026-01-21 13:54:36'),
(4, 'user', 'SDO Employee - Can file and track own requests', '{\"requests.file\": true, \"requests.own\": true}', 1, '2026-01-21 13:54:36');

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
(1, 1, NULL, 'System Administrator', 'Superadmin', 'SDO San Pedro City', 'joerenz.dev@gmail.com', '$2y$10$i9CNq.Jmk./rwMFDdjmyLeCfp6xaYmHyczadTu5Ppo8p6ZJrwxQDm', NULL, NULL, 'active', 1, '2026-01-21 22:12:48', NULL, '2026-01-21 13:54:36');

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
  `travel_category` enum('official','personal') NOT NULL,
  `travel_scope` enum('local','national') DEFAULT NULL,
  `status` enum('pending','approved','disapproved') DEFAULT 'pending',
  `user_id` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `status` enum('pending','approved','disapproved') DEFAULT 'pending',
  `user_id` int(11) NOT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(4, '361a22966e2c80b54e195b0bb4c8d1e5ef2123bfafddfe55323f0c0e59c6eff9', 1, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '::1', '2026-01-21 23:14:40', '2026-01-21 14:12:48');

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
(1, 'LS', 2026, 0),
(2, 'AT-LOCAL', 2026, 0),
(3, 'AT-NATL', 2026, 0),
(4, 'AT-PERS', 2026, 0);

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
  ADD KEY `idx_created` (`created_at`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `admin_roles`
--
ALTER TABLE `admin_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `admin_users`
--
ALTER TABLE `admin_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `authority_to_travel`
--
ALTER TABLE `authority_to_travel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `locator_slips`
--
ALTER TABLE `locator_slips`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `session_tokens`
--
ALTER TABLE `session_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tracking_sequences`
--
ALTER TABLE `tracking_sequences`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
