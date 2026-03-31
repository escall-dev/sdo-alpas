-- =====================================================
-- SDO ATLAS DATABASE MIGRATION
-- Authority to Travel Routing System Update
-- Run this script to update an existing database
-- =====================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+08:00";

USE `sdo_atlas`;

-- =========================
-- STEP 1: Backup existing data
-- =========================
-- CREATE TABLE admin_roles_backup AS SELECT * FROM admin_roles;
-- CREATE TABLE authority_to_travel_backup AS SELECT * FROM authority_to_travel;

-- =========================
-- STEP 2: Update admin_roles
-- =========================

-- First, update existing roles
UPDATE admin_roles SET 
    description = 'Schools Division Superintendent - Full system access and executive override',
    permissions = '{"all": true}'
WHERE id = 1;

UPDATE admin_roles SET 
    description = 'Assistant Schools Division Superintendent - Final approver for all travel requests',
    permissions = '{"requests.view": true, "requests.approve": true, "requests.final_approve": true, "logs.view": true, "analytics.view": true}'
WHERE id = 2;

-- Change role 3 from AOV to OSDS_CHIEF
UPDATE admin_roles SET 
    role_name = 'OSDS_CHIEF',
    description = 'Administrative Officer V - Recommending authority for OSDS units (Supply, Records, HR, Admin)',
    permissions = '{"requests.view": true, "requests.recommend": true, "requests.own": true, "logs.view": true}'
WHERE id = 3;

-- Change role 4 from USER to CID_CHIEF
UPDATE admin_roles SET 
    role_name = 'CID_CHIEF',
    description = 'Chief, Curriculum Implementation Division - Recommending authority for CID',
    permissions = '{"requests.view": true, "requests.recommend": true, "requests.own": true, "logs.view": true}'
WHERE id = 4;

-- Add new roles if they don't exist
INSERT INTO admin_roles (id, role_name, description, permissions) VALUES
(5, 'SGOD_CHIEF', 'Chief, School Governance and Operations Division - Recommending authority for SGOD', '{"requests.view": true, "requests.recommend": true, "requests.own": true, "logs.view": true}'),
(6, 'USER', 'SDO Employee - Can file and track own requests', '{"requests.file": true, "requests.own": true}')
ON DUPLICATE KEY UPDATE 
    role_name = VALUES(role_name),
    description = VALUES(description),
    permissions = VALUES(permissions);

-- =========================
-- STEP 3: Update authority_to_travel table
-- =========================

-- Add new columns for routing if they don't exist
ALTER TABLE authority_to_travel 
    ADD COLUMN IF NOT EXISTS recommended_by INT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS current_approver_role VARCHAR(50) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS routing_stage ENUM('recommending','final','completed') DEFAULT 'recommending',
    ADD COLUMN IF NOT EXISTS requester_office VARCHAR(150),
    ADD COLUMN IF NOT EXISTS requester_role_id INT;

-- Add foreign key for recommended_by if not exists
-- Note: You may need to run this separately if the constraint already exists
-- ALTER TABLE authority_to_travel ADD CONSTRAINT fk_recommended_by FOREIGN KEY (recommended_by) REFERENCES admin_users(id);

-- Add indexes for routing
ALTER TABLE authority_to_travel 
    ADD INDEX IF NOT EXISTS idx_routing (current_approver_role, routing_stage),
    ADD INDEX IF NOT EXISTS idx_requester_office (requester_office);

-- Update status ENUM to include 'recommended'
ALTER TABLE authority_to_travel 
    MODIFY COLUMN status ENUM('pending','recommended','approved','disapproved') DEFAULT 'pending';

-- Set default travel_category
ALTER TABLE authority_to_travel 
    MODIFY COLUMN travel_category ENUM('official','personal') NOT NULL DEFAULT 'official';

-- =========================
-- STEP 4: Update existing AT records with requester info
-- =========================

-- Update requester_office and requester_role_id from user data
UPDATE authority_to_travel at
JOIN admin_users u ON at.user_id = u.id
SET at.requester_office = u.employee_office,
    at.requester_role_id = u.role_id
WHERE at.requester_office IS NULL OR at.requester_office = '';

-- Set routing_stage for existing approved/disapproved records
UPDATE authority_to_travel 
SET routing_stage = 'completed', current_approver_role = NULL 
WHERE status IN ('approved', 'disapproved');

-- Set routing_stage for existing pending records (route to ASDS by default)
UPDATE authority_to_travel 
SET routing_stage = 'final', current_approver_role = 'ASDS' 
WHERE status = 'pending';

-- =========================
-- STEP 5: Update tracking_sequences
-- =========================

-- Add unified AT tracking sequence if not exists
INSERT INTO tracking_sequences (prefix, year, last_number) 
SELECT 'AT', YEAR(CURDATE()), COALESCE(
    (SELECT MAX(last_number) FROM tracking_sequences WHERE prefix LIKE 'AT%' AND year = YEAR(CURDATE())),
    0
)
ON DUPLICATE KEY UPDATE last_number = last_number;

-- =========================
-- STEP 6: Update existing users with old role_id = 4 (was USER, now CID_CHIEF)
-- =========================
-- WARNING: Review this carefully before running!
-- Users who were previously role_id=4 (USER) will now be CID_CHIEF
-- You need to manually reassign them to role_id=6 (new USER)

-- Option A: Move all old USER (role_id=4) to new USER (role_id=6)
-- UPDATE admin_users SET role_id = 6 WHERE role_id = 4;

-- Option B: Check and manually assign based on employee_position/employee_office
-- SELECT id, full_name, employee_position, employee_office, role_id FROM admin_users WHERE role_id = 4;

-- =========================
-- ROUTING LOGIC REFERENCE
-- =========================
-- Unit Head Offices Mapping (defined in admin_config.php):
-- ROLE_CID_CHIEF (4) supervises: CID
-- ROLE_SGOD_CHIEF (5) supervises: SGOD  
-- ROLE_OSDS_CHIEF (3) supervises: OSDS, Supply, Records, HR, Admin, Personnel, Cashier, Finance
--
-- When a user from CID files AT -> routes to CID_CHIEF only
-- When a user from SGOD files AT -> routes to SGOD_CHIEF only
-- When a user from Supply/Records/etc files AT -> routes to OSDS_CHIEF only
-- Each unit head ONLY sees requests from their own units

-- =========================
-- VERIFICATION QUERIES
-- =========================

-- Check roles
-- SELECT * FROM admin_roles ORDER BY id;

-- Check AT table structure
-- DESCRIBE authority_to_travel;

-- Check tracking sequences
-- SELECT * FROM tracking_sequences WHERE prefix LIKE 'AT%';

-- Check user role assignments
-- SELECT au.id, au.full_name, au.employee_office, ar.role_name 
-- FROM admin_users au 
-- JOIN admin_roles ar ON au.role_id = ar.id 
-- ORDER BY ar.id, au.full_name;

-- Check AT requests with routing info
-- SELECT at.id, at.at_tracking_no, at.requester_office, at.current_approver_role, at.routing_stage, at.status
-- FROM authority_to_travel at
-- ORDER BY at.created_at DESC;

