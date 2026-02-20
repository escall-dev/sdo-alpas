-- Migration: Guard Role & Pass Slip Time Tracking
-- Adds ROLE_GUARD, guard time-recording columns, and VL deduction tracking
-- Date: 2026-02-20

-- 1. Add Guard role to admin_roles
INSERT INTO admin_roles (id, role_name, description)
VALUES (8, 'GUARD', 'Guard on Duty - Pass Slip monitoring')
ON DUPLICATE KEY UPDATE role_name = 'GUARD', description = 'Guard on Duty - Pass Slip monitoring';

-- 2. Add guard tracking columns to pass_slips
ALTER TABLE pass_slips
    ADD COLUMN guard_departed_by INT NULL DEFAULT NULL COMMENT 'Guard who recorded departure' AFTER actual_arrival_time,
    ADD COLUMN guard_arrived_by INT NULL DEFAULT NULL COMMENT 'Guard who recorded arrival' AFTER guard_departed_by,
    ADD COLUMN departed_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Exact timestamp of departure button click' AFTER guard_arrived_by,
    ADD COLUMN arrived_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Exact timestamp of arrival button click' AFTER departed_at,
    ADD COLUMN excess_hours DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Hours late beyond IAT (if any)' AFTER arrived_at,
    ADD COLUMN vl_deduction_note VARCHAR(255) NULL DEFAULT NULL COMMENT 'VL deduction flag when 8hrs accumulated' AFTER excess_hours;

-- 3. Add foreign keys for guard columns
ALTER TABLE pass_slips
    ADD CONSTRAINT fk_ps_guard_departed FOREIGN KEY (guard_departed_by) REFERENCES admin_users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_ps_guard_arrived FOREIGN KEY (guard_arrived_by) REFERENCES admin_users(id) ON DELETE SET NULL;

-- 4. Add index for accumulated hours queries (lookup by user with completed guard times)
CREATE INDEX idx_ps_guard_times ON pass_slips (user_id, status, actual_departure_time, actual_arrival_time);

-- 5. Add permissions for guard role in admin_role_permissions (if table exists)
-- Guards get minimal permissions: view pass slips and update guard times
INSERT IGNORE INTO admin_role_permissions (role_id, permission)
SELECT 8, 'requests.own'
FROM dual
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'admin_role_permissions');

INSERT IGNORE INTO admin_role_permissions (role_id, permission)
SELECT 8, 'requests.view'
FROM dual
WHERE EXISTS (SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'admin_role_permissions');
