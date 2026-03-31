-- Pass Slips Module - Database Migration
-- SDO ATLAS
-- Run this migration to add Pass Slip support

-- Create pass_slips table
CREATE TABLE IF NOT EXISTS pass_slips (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ps_control_no VARCHAR(30) NOT NULL UNIQUE,
  employee_name VARCHAR(150) NOT NULL,
  employee_position VARCHAR(100) DEFAULT NULL,
  employee_office VARCHAR(100) DEFAULT NULL,
  date DATE NOT NULL,
  destination VARCHAR(255) NOT NULL,
  idt TIME NOT NULL,
  iat TIME NOT NULL,
  purpose TEXT NOT NULL,
  status ENUM('pending','approved','disapproved','cancelled') DEFAULT 'pending',
  user_id INT NOT NULL,
  assigned_approver_role_id INT DEFAULT NULL,
  assigned_approver_user_id INT DEFAULT NULL,
  approved_by INT DEFAULT NULL,
  approver_name VARCHAR(150) DEFAULT NULL,
  approver_position VARCHAR(150) DEFAULT NULL,
  approval_date DATE DEFAULT NULL,
  approving_time TIME DEFAULT NULL,
  rejection_reason TEXT DEFAULT NULL,
  cancelled_at TIMESTAMP NULL DEFAULT NULL,
  cancelled_by INT DEFAULT NULL,
  actual_departure_time TIME DEFAULT NULL,
  actual_arrival_time TIME DEFAULT NULL,
  requesting_employee_name VARCHAR(150) DEFAULT NULL,
  request_date DATE DEFAULT NULL,
  oic_approved TINYINT(1) DEFAULT 0,
  oic_approver_name VARCHAR(150) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES admin_users(id) ON DELETE CASCADE,
  FOREIGN KEY (approved_by) REFERENCES admin_users(id) ON DELETE SET NULL,
  FOREIGN KEY (cancelled_by) REFERENCES admin_users(id) ON DELETE SET NULL,
  INDEX idx_ps_status (status),
  INDEX idx_ps_user (user_id),
  INDEX idx_ps_approver (assigned_approver_user_id),
  INDEX idx_ps_date (date),
  INDEX idx_ps_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add PS tracking sequence
INSERT IGNORE INTO tracking_sequences (prefix, year, last_number)
VALUES ('PS', YEAR(CURDATE()), 0);
