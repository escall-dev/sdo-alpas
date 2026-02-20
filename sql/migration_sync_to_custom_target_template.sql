-- ============================================================
-- ALPAS -> CUSTOM TARGET DB SYNC TEMPLATE
-- ============================================================
-- HOW TO USE:
-- 1) Replace these placeholders everywhere in this file:
--    {{TARGET_DB}}           e.g. hr_dashboard
--    {{TARGET_TABLE}}        e.g. unified_travel_feed
--    {{COL_EMP_NO}}          e.g. employee_no
--    {{COL_FULL_NAME}}       e.g. full_name
--    {{COL_TRAVEL_TYPE}}     e.g. travel_type
--    {{COL_DATE_FILED}}      e.g. date_filed
--    {{COL_DEPARTURE_DATE}}  e.g. departure_date
--    {{COL_ARRIVAL_DATE}}    e.g. arrival_date
--    {{COL_DEPARTURE_TIME}}  e.g. departure_time
--    {{COL_ARRIVAL_TIME}}    e.g. arrival_time
--    {{COL_SOURCE_TABLE}}    e.g. source_table
--    {{COL_SOURCE_ID}}       e.g. source_id
--    {{COL_CREATED_AT}}      e.g. created_at
--    {{COL_UPDATED_AT}}      e.g. updated_at
--
-- 2) Ensure target table has unique key for upsert safety:
--      UNIQUE KEY ({{COL_SOURCE_TABLE}}, {{COL_SOURCE_ID}})
--
-- 3) Run this file in phpMyAdmin / mysql client.
--
-- NOTE:
-- - Works when source + target DB are on the SAME MySQL/MariaDB server.
-- - If target is on another server, use API/ETL/app-level sync.

USE sdo_atlas;

-- ---------- CLEANUP ----------
DROP TRIGGER IF EXISTS trg_authority_to_travel_ai_to_custom;
DROP TRIGGER IF EXISTS trg_authority_to_travel_au_to_custom;
DROP TRIGGER IF EXISTS trg_locator_slips_ai_to_custom;
DROP TRIGGER IF EXISTS trg_locator_slips_au_to_custom;
DROP TRIGGER IF EXISTS trg_pass_slips_ai_to_custom;
DROP TRIGGER IF EXISTS trg_pass_slips_au_to_custom;

DROP EVENT IF EXISTS ev_sync_authority_to_travel_to_custom;
DROP EVENT IF EXISTS ev_sync_locator_slips_to_custom;
DROP EVENT IF EXISTS ev_sync_pass_slips_to_custom;

DELIMITER $$

-- ============================================================
-- AUTHORITY TO TRAVEL
-- ============================================================
CREATE TRIGGER trg_authority_to_travel_ai_to_custom
AFTER INSERT ON authority_to_travel
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE {{TARGET_DB}}.{{TARGET_TABLE}}
           SET {{COL_EMP_NO}} = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
               {{COL_FULL_NAME}} = NEW.employee_name,
               {{COL_TRAVEL_TYPE}} = 'Authority to Travel',
               {{COL_DATE_FILED}} = NEW.date_filed,
               {{COL_DEPARTURE_DATE}} = NEW.date_from,
               {{COL_ARRIVAL_DATE}} = NEW.date_to,
               {{COL_DEPARTURE_TIME}} = '00:00:00',
               {{COL_ARRIVAL_TIME}} = '00:00:00',
               {{COL_UPDATED_AT}} = NOW()
         WHERE {{COL_SOURCE_TABLE}} = 'authority_to_travel'
           AND {{COL_SOURCE_ID}} = NEW.id;

        IF ROW_COUNT() = 0 THEN
            INSERT INTO {{TARGET_DB}}.{{TARGET_TABLE}} (
                {{COL_EMP_NO}}, {{COL_FULL_NAME}}, {{COL_TRAVEL_TYPE}}, {{COL_DATE_FILED}},
                {{COL_DEPARTURE_DATE}}, {{COL_ARRIVAL_DATE}}, {{COL_DEPARTURE_TIME}}, {{COL_ARRIVAL_TIME}},
                {{COL_SOURCE_TABLE}}, {{COL_SOURCE_ID}}, {{COL_CREATED_AT}}, {{COL_UPDATED_AT}}
            ) VALUES (
                (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
                NEW.employee_name,
                'Authority to Travel',
                NEW.date_filed,
                NEW.date_from,
                NEW.date_to,
                '00:00:00',
                '00:00:00',
                'authority_to_travel',
                NEW.id,
                NOW(),
                NOW()
            );
        END IF;
    END IF;
END$$

CREATE TRIGGER trg_authority_to_travel_au_to_custom
AFTER UPDATE ON authority_to_travel
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE {{TARGET_DB}}.{{TARGET_TABLE}}
           SET {{COL_EMP_NO}} = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
               {{COL_FULL_NAME}} = NEW.employee_name,
               {{COL_TRAVEL_TYPE}} = 'Authority to Travel',
               {{COL_DATE_FILED}} = NEW.date_filed,
               {{COL_DEPARTURE_DATE}} = NEW.date_from,
               {{COL_ARRIVAL_DATE}} = NEW.date_to,
               {{COL_DEPARTURE_TIME}} = '00:00:00',
               {{COL_ARRIVAL_TIME}} = '00:00:00',
               {{COL_UPDATED_AT}} = NOW()
         WHERE {{COL_SOURCE_TABLE}} = 'authority_to_travel'
           AND {{COL_SOURCE_ID}} = NEW.id;

        IF ROW_COUNT() = 0 THEN
            INSERT INTO {{TARGET_DB}}.{{TARGET_TABLE}} (
                {{COL_EMP_NO}}, {{COL_FULL_NAME}}, {{COL_TRAVEL_TYPE}}, {{COL_DATE_FILED}},
                {{COL_DEPARTURE_DATE}}, {{COL_ARRIVAL_DATE}}, {{COL_DEPARTURE_TIME}}, {{COL_ARRIVAL_TIME}},
                {{COL_SOURCE_TABLE}}, {{COL_SOURCE_ID}}, {{COL_CREATED_AT}}, {{COL_UPDATED_AT}}
            ) VALUES (
                (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
                NEW.employee_name,
                'Authority to Travel',
                NEW.date_filed,
                NEW.date_from,
                NEW.date_to,
                '00:00:00',
                '00:00:00',
                'authority_to_travel',
                NEW.id,
                NOW(),
                NOW()
            );
        END IF;
    ELSE
        DELETE FROM {{TARGET_DB}}.{{TARGET_TABLE}}
         WHERE {{COL_SOURCE_TABLE}} = 'authority_to_travel'
           AND {{COL_SOURCE_ID}} = NEW.id;
    END IF;
END$$

-- ============================================================
-- LOCATOR SLIPS
-- ============================================================
CREATE TRIGGER trg_locator_slips_ai_to_custom
AFTER INSERT ON locator_slips
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE {{TARGET_DB}}.{{TARGET_TABLE}}
           SET {{COL_EMP_NO}} = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
               {{COL_FULL_NAME}} = NEW.employee_name,
               {{COL_TRAVEL_TYPE}} = 'Locator Slip',
               {{COL_DATE_FILED}} = COALESCE(NEW.date_filed, NEW.request_date, DATE(NEW.created_at)),
               {{COL_DEPARTURE_DATE}} = DATE(COALESCE(NEW.date_time, NEW.created_at)),
               {{COL_ARRIVAL_DATE}} = DATE(COALESCE(NEW.date_time, NEW.created_at)),
               {{COL_DEPARTURE_TIME}} = TIME(COALESCE(NEW.date_time, NEW.created_at)),
               {{COL_ARRIVAL_TIME}} = TIME(COALESCE(NEW.date_time, NEW.created_at)),
               {{COL_UPDATED_AT}} = NOW()
         WHERE {{COL_SOURCE_TABLE}} = 'locator_slips'
           AND {{COL_SOURCE_ID}} = NEW.id;

        IF ROW_COUNT() = 0 THEN
            INSERT INTO {{TARGET_DB}}.{{TARGET_TABLE}} (
                {{COL_EMP_NO}}, {{COL_FULL_NAME}}, {{COL_TRAVEL_TYPE}}, {{COL_DATE_FILED}},
                {{COL_DEPARTURE_DATE}}, {{COL_ARRIVAL_DATE}}, {{COL_DEPARTURE_TIME}}, {{COL_ARRIVAL_TIME}},
                {{COL_SOURCE_TABLE}}, {{COL_SOURCE_ID}}, {{COL_CREATED_AT}}, {{COL_UPDATED_AT}}
            ) VALUES (
                (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
                NEW.employee_name,
                'Locator Slip',
                COALESCE(NEW.date_filed, NEW.request_date, DATE(NEW.created_at)),
                DATE(COALESCE(NEW.date_time, NEW.created_at)),
                DATE(COALESCE(NEW.date_time, NEW.created_at)),
                TIME(COALESCE(NEW.date_time, NEW.created_at)),
                TIME(COALESCE(NEW.date_time, NEW.created_at)),
                'locator_slips',
                NEW.id,
                NOW(),
                NOW()
            );
        END IF;
    END IF;
END$$

CREATE TRIGGER trg_locator_slips_au_to_custom
AFTER UPDATE ON locator_slips
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE {{TARGET_DB}}.{{TARGET_TABLE}}
           SET {{COL_EMP_NO}} = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
               {{COL_FULL_NAME}} = NEW.employee_name,
               {{COL_TRAVEL_TYPE}} = 'Locator Slip',
               {{COL_DATE_FILED}} = COALESCE(NEW.date_filed, NEW.request_date, DATE(NEW.created_at)),
               {{COL_DEPARTURE_DATE}} = DATE(COALESCE(NEW.date_time, NEW.created_at)),
               {{COL_ARRIVAL_DATE}} = DATE(COALESCE(NEW.date_time, NEW.created_at)),
               {{COL_DEPARTURE_TIME}} = TIME(COALESCE(NEW.date_time, NEW.created_at)),
               {{COL_ARRIVAL_TIME}} = TIME(COALESCE(NEW.date_time, NEW.created_at)),
               {{COL_UPDATED_AT}} = NOW()
         WHERE {{COL_SOURCE_TABLE}} = 'locator_slips'
           AND {{COL_SOURCE_ID}} = NEW.id;

        IF ROW_COUNT() = 0 THEN
            INSERT INTO {{TARGET_DB}}.{{TARGET_TABLE}} (
                {{COL_EMP_NO}}, {{COL_FULL_NAME}}, {{COL_TRAVEL_TYPE}}, {{COL_DATE_FILED}},
                {{COL_DEPARTURE_DATE}}, {{COL_ARRIVAL_DATE}}, {{COL_DEPARTURE_TIME}}, {{COL_ARRIVAL_TIME}},
                {{COL_SOURCE_TABLE}}, {{COL_SOURCE_ID}}, {{COL_CREATED_AT}}, {{COL_UPDATED_AT}}
            ) VALUES (
                (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
                NEW.employee_name,
                'Locator Slip',
                COALESCE(NEW.date_filed, NEW.request_date, DATE(NEW.created_at)),
                DATE(COALESCE(NEW.date_time, NEW.created_at)),
                DATE(COALESCE(NEW.date_time, NEW.created_at)),
                TIME(COALESCE(NEW.date_time, NEW.created_at)),
                TIME(COALESCE(NEW.date_time, NEW.created_at)),
                'locator_slips',
                NEW.id,
                NOW(),
                NOW()
            );
        END IF;
    ELSE
        DELETE FROM {{TARGET_DB}}.{{TARGET_TABLE}}
         WHERE {{COL_SOURCE_TABLE}} = 'locator_slips'
           AND {{COL_SOURCE_ID}} = NEW.id;
    END IF;
END$$

-- ============================================================
-- PASS SLIPS
-- ============================================================
CREATE TRIGGER trg_pass_slips_ai_to_custom
AFTER INSERT ON pass_slips
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE {{TARGET_DB}}.{{TARGET_TABLE}}
           SET {{COL_EMP_NO}} = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
               {{COL_FULL_NAME}} = NEW.employee_name,
               {{COL_TRAVEL_TYPE}} = 'Pass Slip',
               {{COL_DATE_FILED}} = COALESCE(NEW.request_date, NEW.date, DATE(NEW.created_at)),
               {{COL_DEPARTURE_DATE}} = COALESCE(NEW.date, DATE(NEW.created_at)),
               {{COL_ARRIVAL_DATE}} = COALESCE(NEW.date, DATE(NEW.created_at)),
               {{COL_DEPARTURE_TIME}} = COALESCE(NEW.idt, '00:00:00'),
               {{COL_ARRIVAL_TIME}} = COALESCE(NEW.iat, '00:00:00'),
               {{COL_UPDATED_AT}} = NOW()
         WHERE {{COL_SOURCE_TABLE}} = 'pass_slips'
           AND {{COL_SOURCE_ID}} = NEW.id;

        IF ROW_COUNT() = 0 THEN
            INSERT INTO {{TARGET_DB}}.{{TARGET_TABLE}} (
                {{COL_EMP_NO}}, {{COL_FULL_NAME}}, {{COL_TRAVEL_TYPE}}, {{COL_DATE_FILED}},
                {{COL_DEPARTURE_DATE}}, {{COL_ARRIVAL_DATE}}, {{COL_DEPARTURE_TIME}}, {{COL_ARRIVAL_TIME}},
                {{COL_SOURCE_TABLE}}, {{COL_SOURCE_ID}}, {{COL_CREATED_AT}}, {{COL_UPDATED_AT}}
            ) VALUES (
                (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
                NEW.employee_name,
                'Pass Slip',
                COALESCE(NEW.request_date, NEW.date, DATE(NEW.created_at)),
                COALESCE(NEW.date, DATE(NEW.created_at)),
                COALESCE(NEW.date, DATE(NEW.created_at)),
                COALESCE(NEW.idt, '00:00:00'),
                COALESCE(NEW.iat, '00:00:00'),
                'pass_slips',
                NEW.id,
                NOW(),
                NOW()
            );
        END IF;
    END IF;
END$$

CREATE TRIGGER trg_pass_slips_au_to_custom
AFTER UPDATE ON pass_slips
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE {{TARGET_DB}}.{{TARGET_TABLE}}
           SET {{COL_EMP_NO}} = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
               {{COL_FULL_NAME}} = NEW.employee_name,
               {{COL_TRAVEL_TYPE}} = 'Pass Slip',
               {{COL_DATE_FILED}} = COALESCE(NEW.request_date, NEW.date, DATE(NEW.created_at)),
               {{COL_DEPARTURE_DATE}} = COALESCE(NEW.date, DATE(NEW.created_at)),
               {{COL_ARRIVAL_DATE}} = COALESCE(NEW.date, DATE(NEW.created_at)),
               {{COL_DEPARTURE_TIME}} = COALESCE(NEW.idt, '00:00:00'),
               {{COL_ARRIVAL_TIME}} = COALESCE(NEW.iat, '00:00:00'),
               {{COL_UPDATED_AT}} = NOW()
         WHERE {{COL_SOURCE_TABLE}} = 'pass_slips'
           AND {{COL_SOURCE_ID}} = NEW.id;

        IF ROW_COUNT() = 0 THEN
            INSERT INTO {{TARGET_DB}}.{{TARGET_TABLE}} (
                {{COL_EMP_NO}}, {{COL_FULL_NAME}}, {{COL_TRAVEL_TYPE}}, {{COL_DATE_FILED}},
                {{COL_DEPARTURE_DATE}}, {{COL_ARRIVAL_DATE}}, {{COL_DEPARTURE_TIME}}, {{COL_ARRIVAL_TIME}},
                {{COL_SOURCE_TABLE}}, {{COL_SOURCE_ID}}, {{COL_CREATED_AT}}, {{COL_UPDATED_AT}}
            ) VALUES (
                (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
                NEW.employee_name,
                'Pass Slip',
                COALESCE(NEW.request_date, NEW.date, DATE(NEW.created_at)),
                COALESCE(NEW.date, DATE(NEW.created_at)),
                COALESCE(NEW.date, DATE(NEW.created_at)),
                COALESCE(NEW.idt, '00:00:00'),
                COALESCE(NEW.iat, '00:00:00'),
                'pass_slips',
                NEW.id,
                NOW(),
                NOW()
            );
        END IF;
    ELSE
        DELETE FROM {{TARGET_DB}}.{{TARGET_TABLE}}
         WHERE {{COL_SOURCE_TABLE}} = 'pass_slips'
           AND {{COL_SOURCE_ID}} = NEW.id;
    END IF;
END$$

-- ============================================================
-- RECONCILIATION EVENTS (every 5 min)
-- ============================================================
CREATE EVENT ev_sync_authority_to_travel_to_custom
ON SCHEDULE EVERY 5 MINUTE
DO
BEGIN
    UPDATE {{TARGET_DB}}.{{TARGET_TABLE}} d
    JOIN sdo_atlas.authority_to_travel s
      ON d.{{COL_SOURCE_TABLE}} = 'authority_to_travel'
     AND d.{{COL_SOURCE_ID}} = s.id
       SET d.{{COL_EMP_NO}} = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = s.user_id LIMIT 1),
           d.{{COL_FULL_NAME}} = s.employee_name,
           d.{{COL_TRAVEL_TYPE}} = 'Authority to Travel',
           d.{{COL_DATE_FILED}} = s.date_filed,
           d.{{COL_DEPARTURE_DATE}} = s.date_from,
           d.{{COL_ARRIVAL_DATE}} = s.date_to,
           d.{{COL_DEPARTURE_TIME}} = '00:00:00',
           d.{{COL_ARRIVAL_TIME}} = '00:00:00',
           d.{{COL_UPDATED_AT}} = NOW()
     WHERE s.status = 'approved';
END$$

CREATE EVENT ev_sync_locator_slips_to_custom
ON SCHEDULE EVERY 5 MINUTE
DO
BEGIN
    UPDATE {{TARGET_DB}}.{{TARGET_TABLE}} d
    JOIN sdo_atlas.locator_slips s
      ON d.{{COL_SOURCE_TABLE}} = 'locator_slips'
     AND d.{{COL_SOURCE_ID}} = s.id
       SET d.{{COL_EMP_NO}} = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = s.user_id LIMIT 1),
           d.{{COL_FULL_NAME}} = s.employee_name,
           d.{{COL_TRAVEL_TYPE}} = 'Locator Slip',
           d.{{COL_DATE_FILED}} = COALESCE(s.date_filed, s.request_date, DATE(s.created_at)),
           d.{{COL_DEPARTURE_DATE}} = DATE(COALESCE(s.date_time, s.created_at)),
           d.{{COL_ARRIVAL_DATE}} = DATE(COALESCE(s.date_time, s.created_at)),
           d.{{COL_DEPARTURE_TIME}} = TIME(COALESCE(s.date_time, s.created_at)),
           d.{{COL_ARRIVAL_TIME}} = TIME(COALESCE(s.date_time, s.created_at)),
           d.{{COL_UPDATED_AT}} = NOW()
     WHERE s.status = 'approved';
END$$

CREATE EVENT ev_sync_pass_slips_to_custom
ON SCHEDULE EVERY 5 MINUTE
DO
BEGIN
    UPDATE {{TARGET_DB}}.{{TARGET_TABLE}} d
    JOIN sdo_atlas.pass_slips s
      ON d.{{COL_SOURCE_TABLE}} = 'pass_slips'
     AND d.{{COL_SOURCE_ID}} = s.id
       SET d.{{COL_EMP_NO}} = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = s.user_id LIMIT 1),
           d.{{COL_FULL_NAME}} = s.employee_name,
           d.{{COL_TRAVEL_TYPE}} = 'Pass Slip',
           d.{{COL_DATE_FILED}} = COALESCE(s.request_date, s.date, DATE(s.created_at)),
           d.{{COL_DEPARTURE_DATE}} = COALESCE(s.date, DATE(s.created_at)),
           d.{{COL_ARRIVAL_DATE}} = COALESCE(s.date, DATE(s.created_at)),
           d.{{COL_DEPARTURE_TIME}} = COALESCE(s.idt, '00:00:00'),
           d.{{COL_ARRIVAL_TIME}} = COALESCE(s.iat, '00:00:00'),
           d.{{COL_UPDATED_AT}} = NOW()
     WHERE s.status = 'approved';
END$$

DELIMITER ;

-- OPTIONAL: enable event scheduler
-- SET GLOBAL event_scheduler = ON;
