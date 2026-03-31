-- DepEd Order 043 s. 2022 — AT Routing Overhaul Migration
-- Adds 3rd travel scope (international), forwarded_to_ro flag, and final_approver_role column

-- 1. Expand authority_to_travel.travel_scope ENUM to include 'international'
ALTER TABLE authority_to_travel 
    MODIFY COLUMN travel_scope ENUM('local', 'national', 'international') DEFAULT NULL;

-- 2. Add forwarded_to_ro flag (marks ATs that need RD approval outside the system)
ALTER TABLE authority_to_travel 
    ADD COLUMN forwarded_to_ro TINYINT(1) NOT NULL DEFAULT 0 
    COMMENT 'Flags ATs forwarded to Regional Office for RD approval';

-- 3. Add forwarded_to_ro_date
ALTER TABLE authority_to_travel 
    ADD COLUMN forwarded_to_ro_date DATE DEFAULT NULL 
    COMMENT 'Date when AT was forwarded to Regional Office';

-- 4. Add final_approver_role (stores intended final approver at creation time)
ALTER TABLE authority_to_travel 
    ADD COLUMN final_approver_role VARCHAR(50) DEFAULT NULL 
    COMMENT 'Intended final approver role set at routing time (SDS or RD)';

-- 5. Expand unit_routing_config.travel_scope ENUM to include 'national'
ALTER TABLE unit_routing_config 
    MODIFY COLUMN travel_scope ENUM('all', 'local', 'national', 'international') DEFAULT 'all' 
    COMMENT 'Travel scope this routing applies to';

-- 6. Backfill final_approver_role for existing records
-- Existing approved/completed records where SDS approved
UPDATE authority_to_travel 
    SET final_approver_role = 'SDS' 
    WHERE final_approver_role IS NULL AND status IN ('approved', 'disapproved', 'recommended');

-- Existing pending records at final stage (waiting for SDS)
UPDATE authority_to_travel 
    SET final_approver_role = 'SDS' 
    WHERE final_approver_role IS NULL AND routing_stage = 'final';

-- Existing pending records at recommending stage (final will be SDS)
UPDATE authority_to_travel 
    SET final_approver_role = 'SDS' 
    WHERE final_approver_role IS NULL AND routing_stage = 'recommending';
