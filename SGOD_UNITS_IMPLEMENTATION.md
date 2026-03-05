# SGOD Units Implementation Summary

## Overview

Added 7 units under the School Governance and Operations Division (SGOD) led by SGOD Chief Frederick G. Byrd Jr. All these units now route their travel requests directly to the SGOD_CHIEF for recommendation before going to ASDS for final approval.

## SGOD Units Added

1. **SMME** - School Management Monitoring and Evaluation
2. **HRD** - Human Resource Development
3. **SMN** - Social Mobilization and Networking
4. **PR** - Planning and Research
5. **DRRM** - Disaster Risk Reduction and Management
6. **EF** - Education Facilities
7. **SHN** - School Health and Nutrition

## Changes Made

### 1. SQL Migration File Created

**File:** `sql/migration_sgod_units.sql`

This migration:

- Adds 7 SGOD units to the `sdo_offices` table
- Sets parent_office_id = 4 (SGOD division)
- Sets approver_role_id = 5 (SGOD_CHIEF)
- Creates routing configurations in `unit_routing_config` table
- Maps all units to route to SGOD_CHIEF only
- Updates existing user office assignments if applicable

### 2. Configuration File Updated

**File:** `config/admin_config.php`

Updated three key configuration constants:

#### a. SDO_OFFICES Array

Added 7 SGOD units with their full names for office selection in forms:

```php
'SMME' => 'School Management Monitoring and Evaluation',
'HRD' => 'Human Resource Development',
'SMN' => 'Social Mobilization and Networking',
'PR' => 'Planning and Research',
'DRRM' => 'Disaster Risk Reduction and Management',
'EF' => 'Education Facilities',
'SHN' => 'School Health and Nutrition',
```

#### b. ROLE_OFFICE_MAP

Maps each SGOD unit code to SGOD_CHIEF role (role_id=5):

```php
'SMME' => ROLE_SGOD_CHIEF,
'HRD' => ROLE_SGOD_CHIEF,
'SMN' => ROLE_SGOD_CHIEF,
'PR' => ROLE_SGOD_CHIEF,
'DRRM' => ROLE_SGOD_CHIEF,
'EF' => ROLE_SGOD_CHIEF,
'SHN' => ROLE_SGOD_CHIEF,
```

#### c. UNIT_HEAD_OFFICES

Updated SGOD_CHIEF's supervised units:

```php
ROLE_SGOD_CHIEF => ['SGOD', 'SMME', 'HRD', 'SMN', 'PR', 'DRRM', 'EF', 'SHN'],
```

## Routing Flow

### For SGOD Unit Employees

When employees from any of the 7 SGOD units submit an Authority to Travel or Locator Slip request:

1. **Request Filed** → Status: Pending
2. **Routes to SGOD_CHIEF** (Frederick G. Byrd Jr) → Role: Recommending Authority
3. **SGOD_CHIEF Recommends** → Status: Recommended
4. **Routes to ASDS** → Role: Final Approver
5. **ASDS Approves** → Status: Approved → Document Generated

### Bypass for Executives

- **SDS** (Superadmin) can approve directly without routing
- **ASDS** acts as final approver for all requests

## Installation Steps

### 1. Run the SQL Migration

Execute the migration file in your database:

```bash
# Option 1: Using MySQL command line
mysql -u root -p sdo_atlas < sql/migration_sgod_units.sql

# Option 2: Using phpMyAdmin
# - Open phpMyAdmin
# - Select sdo_atlas database
# - Go to SQL tab
# - Copy and paste content from sql/migration_sgod_units.sql
# - Click Go
```

### 2. Verify Installation

Run these verification queries to confirm setup:

```sql
-- Check SGOD units in sdo_offices
SELECT id, office_code, office_name, office_type, parent_office_id, approver_role_id, sort_order
FROM sdo_offices
WHERE parent_office_id = 4 OR office_code = 'SGOD'
ORDER BY sort_order;

-- Check routing configuration
SELECT urc.id, urc.unit_name, urc.unit_display_name,
       urc.approver_role_id, ar.role_name, urc.office_id
FROM unit_routing_config urc
JOIN admin_roles ar ON urc.approver_role_id = ar.id
WHERE urc.approver_role_id = 5
ORDER BY urc.sort_order;
```

Expected results:

- 7 new office records with SGOD as parent
- 7 routing configs pointing to SGOD_CHIEF (role_id=5)

### 3. Test the Routing

1. Create or assign a test user to one of the SGOD units (e.g., "SMME")
2. Have that user file an Authority to Travel request
3. Verify request appears in SGOD_CHIEF's pending queue
4. SGOD_CHIEF recommends the request
5. Verify request then appears in ASDS's pending queue for final approval

## User Assignment

### For New Users

When registering new users or creating accounts:

1. In the employee_office dropdown, select one of the SGOD units:
   - School Management Monitoring and Evaluation
   - Human Resource Development
   - Social Mobilization and Networking
   - Planning and Research
   - Disaster Risk Reduction and Management
   - Education Facilities
   - School Health and Nutrition

2. The system will automatically:
   - Set office_id to the corresponding sdo_offices.id
   - Route their requests to SGOD_CHIEF

### For Existing Users

To move existing users to SGOD units:

```sql
-- Update user's office
UPDATE admin_users
SET office_id = (SELECT id FROM sdo_offices WHERE office_code = 'SMME'),
    employee_office = 'School Management Monitoring and Evaluation'
WHERE id = [user_id];
```

## Files Modified

1. ✅ `sql/migration_sgod_units.sql` (NEW)
2. ✅ `config/admin_config.php` (UPDATED)

## Benefits

✅ **Clear Hierarchy**: SGOD units now properly structured under SGOD Chief
✅ **Simplified Routing**: All SGOD requests go to one approver first
✅ **Accountability**: Frederick G. Byrd Jr oversees all SGOD unit travel
✅ **Scalability**: Easy to add more units or modify routing in future
✅ **Database-Driven**: Changes persist in database, not just code

## Notes

- The migration is idempotent (safe to run multiple times)
- Existing requests are not affected
- Only new requests from SGOD units will follow the new routing
- SGOD_CHIEF user account must exist with role_id = 5
