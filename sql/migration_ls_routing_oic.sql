-- =====================================================
-- SDO ATLAS DATABASE MIGRATION
-- Locator Slip Routing & OIC Delegation System
-- Run this script to update an existing database
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+08:00";

USE `sdo_atlas`;

-- =========================
-- STEP 1: Update locator_slips table for unit-based routing
-- =========================

-- Add routing fields to locator_slips
ALTER TABLE locator_slips 
    ADD COLUMN IF NOT EXISTS assigned_approver_role_id INT DEFAULT NULL COMMENT 'Role ID of the unit head approver',
    ADD COLUMN IF NOT EXISTS assigned_approver_user_id INT DEFAULT NULL COMMENT 'User ID of the assigned approver (can be OIC)',
    ADD COLUMN IF NOT EXISTS requester_office VARCHAR(150) DEFAULT NULL COMMENT 'Office of the requester',
    ADD COLUMN IF NOT EXISTS requester_role_id INT DEFAULT NULL COMMENT 'Role ID of the requester',
    ADD COLUMN IF NOT EXISTS date_filed DATE DEFAULT NULL COMMENT 'Date the request was filed';

-- Add indexes for routing
ALTER TABLE locator_slips 
    ADD INDEX IF NOT EXISTS idx_assigned_approver (assigned_approver_role_id, assigned_approver_user_id),
    ADD INDEX IF NOT EXISTS idx_requester_office (requester_office),
    ADD INDEX IF NOT EXISTS idx_date_filed (date_filed),
    ADD INDEX IF NOT EXISTS idx_approval_date (approval_date);

-- Update existing records with requester info
UPDATE locator_slips ls
JOIN admin_users u ON ls.user_id = u.id
SET ls.requester_office = u.employee_office,
    ls.requester_role_id = u.role_id,
    ls.date_filed = DATE(ls.created_at)
WHERE ls.requester_office IS NULL OR ls.requester_office = '';

-- =========================
-- STEP 2: Create OIC (Officer-In-Charge) delegation table
-- =========================

CREATE TABLE IF NOT EXISTS `oic_delegations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_head_user_id` int(11) NOT NULL COMMENT 'The unit head who is delegating',
  `unit_head_role_id` int(11) NOT NULL COMMENT 'Role ID of the unit head (cid_chief, sgod_chief, osds_chief, etc.)',
  `oic_user_id` int(11) NOT NULL COMMENT 'The user assigned as OIC',
  `start_date` date NOT NULL COMMENT 'Start date of OIC period',
  `end_date` date NOT NULL COMMENT 'End date of OIC period',
  `is_active` tinyint(1) DEFAULT 1 COMMENT 'Whether this delegation is currently active',
  `created_by` int(11) DEFAULT NULL COMMENT 'User who created this delegation',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_unit_head` (`unit_head_user_id`, `unit_head_role_id`),
  KEY `idx_oic_user` (`oic_user_id`),
  KEY `idx_dates` (`start_date`, `end_date`, `is_active`),
  KEY `idx_active` (`is_active`, `start_date`, `end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='OIC Delegation assignments for unit heads';

-- =========================
-- STEP 3: Update authority_to_travel for OIC support
-- =========================

ALTER TABLE authority_to_travel 
    ADD COLUMN IF NOT EXISTS assigned_approver_user_id INT DEFAULT NULL COMMENT 'User ID of the assigned approver (can be OIC)',
    ADD COLUMN IF NOT EXISTS date_filed DATE DEFAULT NULL COMMENT 'Date the request was filed';

-- Add index
ALTER TABLE authority_to_travel 
    ADD INDEX IF NOT EXISTS idx_assigned_approver_user (assigned_approver_user_id),
    ADD INDEX IF NOT EXISTS idx_date_filed (date_filed);

-- Update existing records
UPDATE authority_to_travel 
SET date_filed = DATE(created_at)
WHERE date_filed IS NULL;

-- =========================
-- VERIFICATION QUERIES
-- =========================

-- Check locator_slips structure
-- DESCRIBE locator_slips;

-- Check OIC delegations table
-- DESCRIBE oic_delegations;

-- Check authority_to_travel structure
-- DESCRIBE authority_to_travel;

-- Check active OIC delegations
-- SELECT o.*, 
--        uh.full_name as unit_head_name, uh.employee_office as unit_head_office,
--        oic.full_name as oic_name, oic.employee_office as oic_office
-- FROM oic_delegations o
-- JOIN admin_users uh ON o.unit_head_user_id = uh.id
-- JOIN admin_users oic ON o.oic_user_id = oic.id
-- WHERE o.is_active = 1 
--   AND CURDATE() BETWEEN o.start_date AND o.end_date;
