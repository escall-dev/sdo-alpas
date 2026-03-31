-- =====================================================
-- SDO ATLAS DATABASE SCHEMA (FINAL, PLACEHOLDER-ALIGNED)
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+08:00";

CREATE DATABASE IF NOT EXISTS `sdo_atlas`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `sdo_atlas`;

-- =========================
-- ROLES
-- =========================
DROP TABLE IF EXISTS admin_roles;
CREATE TABLE admin_roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(50) NOT NULL,
  description TEXT,
  permissions JSON,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO admin_roles (id, role_name, description, permissions) VALUES
(1, 'SUPERADMIN', 'System Administrator - Full system access and executive override', '{"all": true}'),
(2, 'ASDS', 'Assistant Schools Division Superintendent - Approves Office Chief locator slips', '{"requests.view": true, "requests.approve": true, "logs.view": true, "analytics.view": true}'),
(3, 'OSDS_CHIEF', 'Administrative Officer V - Recommending authority for OSDS units (Supply, Records, HR, Admin)', '{"requests.view": true, "requests.recommend": true, "requests.own": true, "logs.view": true}'),
(4, 'CID_CHIEF', 'Chief, Curriculum Implementation Division - Recommending authority for CID', '{"requests.view": true, "requests.recommend": true, "requests.own": true, "logs.view": true}'),
(5, 'SGOD_CHIEF', 'Chief, School Governance and Operations Division - Recommending authority for SGOD', '{"requests.view": true, "requests.recommend": true, "requests.own": true, "logs.view": true}'),
(6, 'USER', 'SDO Employee - Can file and track own requests', '{"requests.file": true, "requests.own": true}'),
(7, 'SDS', 'Schools Division Superintendent - Final approver for all travel requests', '{"requests.view": true, "requests.final_approve": true, "logs.view": true, "analytics.view": true}');

-- =========================
-- USERS
-- =========================
DROP TABLE IF EXISTS admin_users;
CREATE TABLE admin_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role_id INT NOT NULL,
  employee_no VARCHAR(50),
  full_name VARCHAR(150) NOT NULL,
  employee_position VARCHAR(100),
  employee_office VARCHAR(150),
  email VARCHAR(100) UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  avatar_url VARCHAR(500),
  google_id VARCHAR(100),
  status ENUM('pending','active','inactive') DEFAULT 'pending',
  is_active TINYINT(1) DEFAULT 1,
  last_login DATETIME,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (role_id) REFERENCES admin_roles(id)
);

-- Default Superadmin Account (password: sdoescall)
INSERT INTO admin_users (role_id, full_name, employee_position, employee_office, email, password_hash, status, is_active) VALUES
(1, 'System Administrator', 'Superadmin', 'SDO San Pedro City', 'joerenz.dev@gmail.com', '$2y$10$i9CNq.Jmk./rwMFDdjmyLeCfp6xaYmHyczadTu5Ppo8p6ZJrwxQDm', 'active', 1);

-- OSDS Chief Account - Paul Jeremy I. Aguja (Locator Slip Approver for OSDS units)
-- Password: sdoescall
INSERT INTO admin_users (role_id, full_name, employee_position, employee_office, email, password_hash, status, is_active) VALUES
(3, 'Paul Jeremy I. Aguja', 'Administrative Officer V', 'OSDS', 'aov@deped.gov.ph', '$2y$10$i9CNq.Jmk./rwMFDdjmyLeCfp6xaYmHyczadTu5Ppo8p6ZJrwxQDm', 'active', 1);

-- =========================
-- SESSION TOKENS (Multi-Account Support)
-- =========================
DROP TABLE IF EXISTS session_tokens;
CREATE TABLE session_tokens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  token VARCHAR(64) UNIQUE NOT NULL,
  user_id INT NOT NULL,
  user_agent VARCHAR(500),
  ip_address VARCHAR(45),
  expires_at DATETIME NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE,
  INDEX idx_token (token),
  INDEX idx_expires (expires_at)
);

-- =========================
-- LOCATOR SLIPS
-- =========================
DROP TABLE IF EXISTS locator_slips;
CREATE TABLE locator_slips (
  id INT AUTO_INCREMENT PRIMARY KEY,

  -- DOCX PLACEHOLDERS
  ls_control_no VARCHAR(30) NOT NULL UNIQUE,
  employee_name VARCHAR(150) NOT NULL,
  employee_position VARCHAR(100),
  employee_office VARCHAR(150),
  purpose_of_travel TEXT NOT NULL,
  travel_type VARCHAR(50) NOT NULL,
  date_time DATETIME NOT NULL,
  destination VARCHAR(255) NOT NULL,
  requesting_employee_name VARCHAR(150),
  request_date DATE,

  approver_name VARCHAR(150),
  approver_position VARCHAR(100),
  approval_date DATE,
  approving_time DATETIME DEFAULT NULL,
  rejection_reason TEXT,

  -- SYSTEM
  status ENUM('pending','approved','disapproved') DEFAULT 'pending',
  user_id INT NOT NULL,
  approved_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES admin_users(id),
  FOREIGN KEY (approved_by) REFERENCES admin_users(id),
  INDEX idx_status (status),
  INDEX idx_user (user_id),
  INDEX idx_created (created_at)
);

-- =========================
-- AUTHORITY TO TRAVEL
-- =========================
DROP TABLE IF EXISTS authority_to_travel;
CREATE TABLE authority_to_travel (
  id INT AUTO_INCREMENT PRIMARY KEY,

  -- DOCX PLACEHOLDERS
  at_tracking_no VARCHAR(30) NOT NULL UNIQUE,
  employee_name VARCHAR(150) NOT NULL,
  employee_position VARCHAR(100),
  permanent_station VARCHAR(150),
  purpose_of_travel TEXT NOT NULL,
  host_of_activity VARCHAR(255),
  date_from DATE NOT NULL,
  date_to DATE NOT NULL,
  destination VARCHAR(255) NOT NULL,
  fund_source VARCHAR(150),
  inclusive_dates VARCHAR(150),

  requesting_employee_name VARCHAR(150),
  request_date DATE,

  recommending_authority_name VARCHAR(150),
  recommending_date DATE,
  recommended_by INT DEFAULT NULL,

  approving_authority_name VARCHAR(150),
  approval_date DATE,
  rejection_reason TEXT,

  -- ROUTING SYSTEM
  current_approver_role VARCHAR(50) DEFAULT NULL,
  routing_stage ENUM('recommending','final','completed') DEFAULT 'recommending',
  requester_office VARCHAR(150),
  requester_role_id INT,

  -- SYSTEM
  travel_category ENUM('official','personal') NOT NULL DEFAULT 'official',
  travel_scope ENUM('local','national') DEFAULT NULL,
  status ENUM('pending','recommended','approved','disapproved') DEFAULT 'pending',
  user_id INT NOT NULL,
  approved_by INT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  FOREIGN KEY (user_id) REFERENCES admin_users(id),
  FOREIGN KEY (approved_by) REFERENCES admin_users(id),
  FOREIGN KEY (recommended_by) REFERENCES admin_users(id),
  INDEX idx_status (status),
  INDEX idx_user (user_id),
  INDEX idx_category (travel_category),
  INDEX idx_scope (travel_scope),
  INDEX idx_routing (current_approver_role, routing_stage),
  INDEX idx_created (created_at)
);

-- =========================
-- TRACKING SEQUENCES
-- =========================
DROP TABLE IF EXISTS tracking_sequences;
CREATE TABLE tracking_sequences (
  id INT AUTO_INCREMENT PRIMARY KEY,
  prefix VARCHAR(20) NOT NULL,
  year INT NOT NULL,
  last_number INT DEFAULT 0,
  UNIQUE KEY (prefix, year)
);

INSERT INTO tracking_sequences (prefix, year, last_number) VALUES
('LS', 2026, 0),
('AT', 2026, 0);

-- =========================
-- ACTIVITY LOGS
-- =========================
DROP TABLE IF EXISTS activity_logs;
CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT,
  action_type VARCHAR(100),
  entity_type VARCHAR(50),
  entity_id INT,
  description TEXT,
  old_value JSON,
  new_value JSON,
  ip_address VARCHAR(45),
  user_agent VARCHAR(500),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE SET NULL,
  INDEX idx_user (user_id),
  INDEX idx_entity (entity_type, entity_id),
  INDEX idx_created (created_at)
);

COMMIT;
