# ALPAS → DTR Sync Instructions and Steps

This document explains how cross-database mirroring is implemented so records from `sdo_atlas` are automatically written to `dtr_system.todtr`.

## 1) What was implemented

Data is mirrored from ALPAS to DTR using:

- **MySQL triggers** (real-time sync on insert/update)
- **MySQL scheduled events** (5-minute reconciliation/safety-net)
- **One-time backfill inserts** (to migrate existing approved records)

Source tables in `sdo_atlas`:

- `authority_to_travel` → `travel_type = 'Authority to Travel'`
- `locator_slips` → `travel_type = 'Locator Slip'`
- `pass_slips` → `travel_type = 'Pass Slip'`

Target table in `dtr_system`:

- `todtr`

## 2) Important schema rule (why travel_type looked blank before)

In `dtr_system.todtr`, `travel_type` is:

```sql
ENUM('Authority to Travel','Locator Slip','Pass Slip')
```

So the sync must write only one of those 3 values. Writing values like `within_region` or `outside_region` will be coerced to empty by MySQL/MariaDB in non-strict mode.

## 3) Files used

- `sql/migration_travel_requests_to_dtr_sync.sql`
  - Handles `authority_to_travel`
- `sql/migration_ls_ps_to_dtr_sync.sql`
  - Handles `locator_slips` and `pass_slips`

## 4) Prerequisites

Use a DB user with privileges on both databases:

- `SELECT` on `sdo_atlas.*`
- `INSERT`, `UPDATE`, `DELETE` on `dtr_system.todtr`
- `TRIGGER` privilege on `sdo_atlas`
- `EVENT` privilege on `sdo_atlas` (if using scheduled events)

Enable event scheduler once (server-level):

```sql
SET GLOBAL event_scheduler = ON;
```

Check:

```sql
SHOW VARIABLES LIKE 'event_scheduler';
```

## 5) Setup steps

### Step 1: Run AT sync migration

Execute:

```sql
SOURCE sql/migration_travel_requests_to_dtr_sync.sql;
```

(or copy/paste in phpMyAdmin SQL tab)

### Step 2: Run LS + PS sync migration

Execute:

```sql
SOURCE sql/migration_ls_ps_to_dtr_sync.sql;
```

### Step 3: Verify triggers were created

```sql
SHOW TRIGGERS FROM sdo_atlas;
```

Expected trigger names include:

- `trg_authority_to_travel_ai_to_dtr`
- `trg_authority_to_travel_au_to_dtr`
- `trg_locator_slips_ai_to_dtr`
- `trg_locator_slips_au_to_dtr`
- `trg_pass_slips_ai_to_dtr`
- `trg_pass_slips_au_to_dtr`

### Step 4: Verify events were created

```sql
SHOW EVENTS FROM sdo_atlas;
```

Expected events include:

- `ev_sync_authority_to_travel_to_dtr`
- `ev_sync_locator_slips_to_dtr`
- `ev_sync_pass_slips_to_dtr`

### Step 5: Verify mirrored data

```sql
SELECT source_table, travel_type, COUNT(*) AS total
FROM dtr_system.todtr
GROUP BY source_table, travel_type
ORDER BY source_table, travel_type;
```

Expected travel_type values are only:

- `Authority to Travel`
- `Locator Slip`
- `Pass Slip`

## 6) Runtime behavior

For each source table:

- If row becomes `approved`:
  - insert/update `dtr_system.todtr`
- If row is no longer `approved` (rejected/cancelled/etc):
  - delete from `dtr_system.todtr`

Events run every 5 minutes to reconcile missed updates.

## 7) Field mapping summary

### authority_to_travel → todtr

- `employee_no` ← `admin_users.employee_no` via `user_id`
- `full_name` ← `employee_name`
- `travel_type` ← `'Authority to Travel'`
- `date_filed` ← `date_filed`
- `departure_date` ← `date_from`
- `arrival_date` ← `date_to`
- `departure_time`, `arrival_time` ← `NULL` / `00:00:00` equivalent in target

### locator_slips → todtr

- `employee_no` ← `admin_users.employee_no`
- `full_name` ← `employee_name`
- `travel_type` ← `'Locator Slip'`
- `date_filed` ← `COALESCE(date_filed, request_date, DATE(created_at))`
- `departure_date`, `arrival_date` ← `DATE(COALESCE(date_time, created_at))`
- `departure_time`, `arrival_time` ← `TIME(COALESCE(date_time, created_at))`

### pass_slips → todtr

- `employee_no` ← `admin_users.employee_no`
- `full_name` ← `employee_name`
- `travel_type` ← `'Pass Slip'`
- `date_filed` ← `COALESCE(request_date, date, DATE(created_at))`
- `departure_date`, `arrival_date` ← `COALESCE(date, DATE(created_at))`
- `departure_time` ← `COALESCE(idt, '00:00:00')`
- `arrival_time` ← `COALESCE(iat, '00:00:00')`

## 8) Troubleshooting

### A) DTR page shows no rows

1. Check approved source rows exist:

```sql
SELECT status, COUNT(*) FROM sdo_atlas.authority_to_travel GROUP BY status;
SELECT status, COUNT(*) FROM sdo_atlas.locator_slips GROUP BY status;
SELECT status, COUNT(*) FROM sdo_atlas.pass_slips GROUP BY status;
```

2. Check target table has rows:

```sql
SELECT COUNT(*) FROM dtr_system.todtr;
```

3. Re-run migrations to recreate triggers/events and re-backfill.

### B) travel_type is blank

Cause: wrong value was written to ENUM column.
Fix: ensure sync writes exactly one of:

- `Authority to Travel`
- `Locator Slip`
- `Pass Slip`

Then backfill:

```sql
UPDATE dtr_system.todtr
SET travel_type = 'Authority to Travel'
WHERE source_table = 'authority_to_travel';

UPDATE dtr_system.todtr
SET travel_type = 'Locator Slip'
WHERE source_table = 'locator_slips';

UPDATE dtr_system.todtr
SET travel_type = 'Pass Slip'
WHERE source_table = 'pass_slips';
```

### C) Events not running

- Verify `event_scheduler = ON`
- Verify DB user has `EVENT` privilege
- Check `SHOW EVENTS FROM sdo_atlas;`

## 9) Notes

- Migrations are written to be re-runnable (`DROP ... IF EXISTS` first).
- Real-time accuracy comes from triggers; events are backup reconciliation.
- DTR mirror is intentionally derived data; ALPAS remains the source of truth.
