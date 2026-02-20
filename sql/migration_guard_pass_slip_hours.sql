-- Migration: Guard role pass slip time tracking + accumulated hours
-- Date: 2026-02-20
-- Description: Add guard departure/arrival tracking columns and VL deduction note to pass_slips table
-- Note: Guard role (id=8) already exists in admin_roles table

-- Add guard tracking columns to pass_slips
ALTER TABLE pass_slips
    ADD COLUMN guard_departed_by INT NULL DEFAULT NULL COMMENT 'Guard who clicked Departed',
    ADD COLUMN guard_arrived_by INT NULL DEFAULT NULL COMMENT 'Guard who clicked Arrived',
    ADD COLUMN departed_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Exact timestamp of departure button click',
    ADD COLUMN arrived_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Exact timestamp of arrival button click',
    ADD COLUMN excess_hours DECIMAL(5,2) NULL DEFAULT NULL COMMENT 'Computed excess beyond IAT if late',
    ADD COLUMN vl_deduction_note VARCHAR(255) NULL DEFAULT NULL COMMENT 'Note when 8hr threshold crossed';

-- Add foreign keys for guard columns
ALTER TABLE pass_slips
    ADD CONSTRAINT fk_ps_guard_departed FOREIGN KEY (guard_departed_by) REFERENCES admin_users(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_ps_guard_arrived FOREIGN KEY (guard_arrived_by) REFERENCES admin_users(id) ON DELETE SET NULL;
