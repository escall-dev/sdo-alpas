-- Migration: Update role names in admin_roles and authority_to_travel
-- Renaming:
--   OSDS_CHIEF -> AO V - ADMINISTRATIVE
--   CID_CHIEF  -> CID CHIEF
--   SGOD_CHIEF -> SGOD CHIEF
--   GUARD      -> GENERAL SERVICES
-- Date: 2026-03-02

-- ===== 1. Update admin_roles table =====
UPDATE admin_roles SET role_name = 'AO V - ADMINISTRATIVE' WHERE id = 3;
UPDATE admin_roles SET role_name = 'CID CHIEF' WHERE id = 4;
UPDATE admin_roles SET role_name = 'SGOD CHIEF' WHERE id = 5;
UPDATE admin_roles SET role_name = 'GENERAL SERVICES' WHERE id = 8;

-- ===== 2. Update authority_to_travel current_approver_role =====
UPDATE authority_to_travel SET current_approver_role = 'AO V - ADMINISTRATIVE' WHERE current_approver_role IN ('OSDS_CHIEF', 'AO V');
UPDATE authority_to_travel SET current_approver_role = 'CID CHIEF' WHERE current_approver_role = 'CID_CHIEF';
UPDATE authority_to_travel SET current_approver_role = 'SGOD CHIEF' WHERE current_approver_role = 'SGOD_CHIEF';

-- ===== 3. Update authority_to_travel final_approver_role =====
UPDATE authority_to_travel SET final_approver_role = 'AO V - ADMINISTRATIVE' WHERE final_approver_role IN ('OSDS_CHIEF', 'AO V');
UPDATE authority_to_travel SET final_approver_role = 'CID CHIEF' WHERE final_approver_role = 'CID_CHIEF';
UPDATE authority_to_travel SET final_approver_role = 'SGOD CHIEF' WHERE final_approver_role = 'SGOD_CHIEF';
