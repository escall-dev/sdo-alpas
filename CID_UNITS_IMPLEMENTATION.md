# CID Units Implementation Summary

## Overview

Added 4 units under the Curriculum Implementation Division (CID) led by CID Chief Erma S. Valenzuela. All these units now route their travel requests directly to the CID_CHIEF for recommendation before going to ASDS for final approval.

## CID Units Added

1. **IM** - Instructional Management
2. **LRM** - Learning Resource Management
3. **ALS** - Alternative Learning System
4. **DIS** - District Instructional Supervision

## Changes Made

### 1. SQL Migration File Created

**File:** `sql/migration_cid_units.sql`

This migration:

- Adds 4 CID units to the `sdo_offices` table
- Sets parent_office_id = 3 (CID division)
- Sets approver_role_id = 4 (CID_CHIEF)
- Creates routing configurations in `unit_routing_config` table
- Maps all units to route to CID_CHIEF only
- Updates existing user office assignments if applicable

### 2. Configuration File Updated

**File:** `config/admin_config.php`

Updated three key configuration constants:

#### a. SDO_OFFICES Array

Added 4 CID units with their full names for office selection in forms:

```php
'IM' => 'Instructional Management',
'LRM' => 'Learning Resource Management',
'ALS' => 'Alternative Learning System',
'DIS' => 'District Instructional Supervision',
```

#### b. ROLE_OFFICE_MAP

Maps each CID unit code to CID_CHIEF role (role_id=4):

```php
'IM' => ROLE_CID_CHIEF,
'LRM' => ROLE_CID_CHIEF,
'ALS' => ROLE_CID_CHIEF,
'DIS' => ROLE_CID_CHIEF,
```

#### c. UNIT_HEAD_OFFICES

Updated CID_CHIEF's supervised units:

```php
ROLE_CID_CHIEF => ['CID', 'IM', 'LRM', 'ALS', 'DIS'],
```

## Routing Flow

### For CID Unit Employees

1. Employee from IM, LRM, ALS, or DIS files a travel request
2. Request is automatically routed to **CID_CHIEF** (Erma S. Valenzuela)
3. CID_CHIEF reviews and recommends the request
4. Request then goes to **ASDS** for final approval

### For CID Division Employees

1. Employee from CID division files a travel request
2. Request is automatically routed to **CID_CHIEF** (Erma S. Valenzuela)
3. CID_CHIEF reviews and recommends the request
4. Request then goes to **ASDS** for final approval

## Database Structure

### sdo_offices Table

The four units are added with:

- `parent_office_id = 3` (CID division)
- `approver_role_id = 4` (CID_CHIEF)
- `office_type = 'unit'`
- `sort_order` values: 50, 51, 52, 53

### unit_routing_config Table

Each unit has a routing configuration entry that:

- Maps the unit to CID_CHIEF (role_id=4)
- Applies to all travel scopes ('all')
- Links to the corresponding `sdo_offices.id`

## Next Steps

1. **Run the Migration**: Execute `sql/migration_cid_units.sql` on your database
2. **Verify Data**: Check that the units appear in the office dropdowns
3. **Test Routing**: Create a test request from one of the CID units and verify it routes to CID_CHIEF
4. **Update Existing Users**: If any users already have these office names, they will be automatically mapped to the new office_id

## Notes

- The migration is idempotent - it can be run multiple times safely
- Existing users with matching office names will be automatically updated
- The units will appear in registration and user management forms automatically
- All routing logic uses the database-driven approach, falling back to static config if needed
