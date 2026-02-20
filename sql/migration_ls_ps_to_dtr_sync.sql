-- Mirror SDO ALPAS Locator Slips and Pass Slips to DTR system
-- Source DB: sdo_atlas
-- Target DB: dtr_system
-- Requires: INSERT/UPDATE on dtr_system.todtr and TRIGGER/EVENT privileges on sdo_atlas

USE sdo_atlas;

DROP TRIGGER IF EXISTS trg_locator_slips_ai_to_dtr;
DROP TRIGGER IF EXISTS trg_locator_slips_au_to_dtr;
DROP TRIGGER IF EXISTS trg_pass_slips_ai_to_dtr;
DROP TRIGGER IF EXISTS trg_pass_slips_au_to_dtr;

DELIMITER $$

CREATE TRIGGER trg_locator_slips_ai_to_dtr
AFTER INSERT ON locator_slips
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE dtr_system.todtr
           SET employee_no = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
               full_name = NEW.employee_name,
               travel_type = 'Locator Slip',
               date_filed = COALESCE(NEW.date_filed, NEW.request_date, DATE(NEW.created_at)),
               departure_date = DATE(COALESCE(NEW.date_time, NEW.created_at)),
               arrival_date = DATE(COALESCE(NEW.date_time, NEW.created_at)),
               departure_time = TIME(COALESCE(NEW.date_time, NEW.created_at)),
               arrival_time = TIME(COALESCE(NEW.date_time, NEW.created_at)),
               updated_at = NOW()
         WHERE source_table = 'locator_slips'
           AND source_id = NEW.id;

        IF ROW_COUNT() = 0 THEN
            INSERT INTO dtr_system.todtr (
                employee_no,
                full_name,
                travel_type,
                date_filed,
                departure_date,
                arrival_date,
                departure_time,
                arrival_time,
                source_table,
                source_id,
                created_at,
                updated_at
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

CREATE TRIGGER trg_locator_slips_au_to_dtr
AFTER UPDATE ON locator_slips
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE dtr_system.todtr
           SET employee_no = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
               full_name = NEW.employee_name,
               travel_type = 'Locator Slip',
               date_filed = COALESCE(NEW.date_filed, NEW.request_date, DATE(NEW.created_at)),
               departure_date = DATE(COALESCE(NEW.date_time, NEW.created_at)),
               arrival_date = DATE(COALESCE(NEW.date_time, NEW.created_at)),
               departure_time = TIME(COALESCE(NEW.date_time, NEW.created_at)),
               arrival_time = TIME(COALESCE(NEW.date_time, NEW.created_at)),
               updated_at = NOW()
         WHERE source_table = 'locator_slips'
           AND source_id = NEW.id;

        IF ROW_COUNT() = 0 THEN
            INSERT INTO dtr_system.todtr (
                employee_no,
                full_name,
                travel_type,
                date_filed,
                departure_date,
                arrival_date,
                departure_time,
                arrival_time,
                source_table,
                source_id,
                created_at,
                updated_at
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
        DELETE FROM dtr_system.todtr
         WHERE source_table = 'locator_slips'
           AND source_id = NEW.id;
    END IF;
END$$

CREATE TRIGGER trg_pass_slips_ai_to_dtr
AFTER INSERT ON pass_slips
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE dtr_system.todtr
           SET employee_no = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
               full_name = NEW.employee_name,
               travel_type = 'Pass Slip',
               date_filed = COALESCE(NEW.request_date, NEW.date, DATE(NEW.created_at)),
               departure_date = COALESCE(NEW.date, DATE(NEW.created_at)),
               arrival_date = COALESCE(NEW.date, DATE(NEW.created_at)),
               departure_time = COALESCE(NEW.idt, '00:00:00'),
               arrival_time = COALESCE(NEW.iat, '00:00:00'),
               updated_at = NOW()
         WHERE source_table = 'pass_slips'
           AND source_id = NEW.id;

        IF ROW_COUNT() = 0 THEN
            INSERT INTO dtr_system.todtr (
                employee_no,
                full_name,
                travel_type,
                date_filed,
                departure_date,
                arrival_date,
                departure_time,
                arrival_time,
                source_table,
                source_id,
                created_at,
                updated_at
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

CREATE TRIGGER trg_pass_slips_au_to_dtr
AFTER UPDATE ON pass_slips
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE dtr_system.todtr
           SET employee_no = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
               full_name = NEW.employee_name,
               travel_type = 'Pass Slip',
               date_filed = COALESCE(NEW.request_date, NEW.date, DATE(NEW.created_at)),
               departure_date = COALESCE(NEW.date, DATE(NEW.created_at)),
               arrival_date = COALESCE(NEW.date, DATE(NEW.created_at)),
               departure_time = COALESCE(NEW.idt, '00:00:00'),
               arrival_time = COALESCE(NEW.iat, '00:00:00'),
               updated_at = NOW()
         WHERE source_table = 'pass_slips'
           AND source_id = NEW.id;

        IF ROW_COUNT() = 0 THEN
            INSERT INTO dtr_system.todtr (
                employee_no,
                full_name,
                travel_type,
                date_filed,
                departure_date,
                arrival_date,
                departure_time,
                arrival_time,
                source_table,
                source_id,
                created_at,
                updated_at
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
        DELETE FROM dtr_system.todtr
         WHERE source_table = 'pass_slips'
           AND source_id = NEW.id;
    END IF;
END$$

DELIMITER ;

DROP EVENT IF EXISTS ev_sync_locator_slips_to_dtr;
DROP EVENT IF EXISTS ev_sync_pass_slips_to_dtr;

DELIMITER $$

CREATE EVENT ev_sync_locator_slips_to_dtr
ON SCHEDULE EVERY 5 MINUTE
DO
BEGIN
    UPDATE dtr_system.todtr d
    JOIN sdo_atlas.locator_slips s
      ON d.source_table = 'locator_slips'
     AND d.source_id = s.id
       SET d.employee_no = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = s.user_id LIMIT 1),
           d.full_name = s.employee_name,
           d.travel_type = 'Locator Slip',
           d.date_filed = COALESCE(s.date_filed, s.request_date, DATE(s.created_at)),
           d.departure_date = DATE(COALESCE(s.date_time, s.created_at)),
           d.arrival_date = DATE(COALESCE(s.date_time, s.created_at)),
           d.departure_time = TIME(COALESCE(s.date_time, s.created_at)),
           d.arrival_time = TIME(COALESCE(s.date_time, s.created_at)),
           d.updated_at = NOW()
     WHERE s.status = 'approved';

    INSERT INTO dtr_system.todtr (
        employee_no,
        full_name,
        travel_type,
        date_filed,
        departure_date,
        arrival_date,
        departure_time,
        arrival_time,
        source_table,
        source_id,
        created_at,
        updated_at
    )
    SELECT
        (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = s.user_id LIMIT 1),
        s.employee_name,
        'Locator Slip',
        COALESCE(s.date_filed, s.request_date, DATE(s.created_at)),
        DATE(COALESCE(s.date_time, s.created_at)),
        DATE(COALESCE(s.date_time, s.created_at)),
        TIME(COALESCE(s.date_time, s.created_at)),
        TIME(COALESCE(s.date_time, s.created_at)),
        'locator_slips',
        s.id,
        NOW(),
        NOW()
    FROM sdo_atlas.locator_slips s
    LEFT JOIN dtr_system.todtr d
      ON d.source_table = 'locator_slips'
     AND d.source_id = s.id
    WHERE s.status = 'approved'
      AND d.source_id IS NULL;

    DELETE d
    FROM dtr_system.todtr d
    LEFT JOIN sdo_atlas.locator_slips s
      ON d.source_table = 'locator_slips'
     AND d.source_id = s.id
    WHERE d.source_table = 'locator_slips'
      AND (s.id IS NULL OR s.status <> 'approved');
END$$

CREATE EVENT ev_sync_pass_slips_to_dtr
ON SCHEDULE EVERY 5 MINUTE
DO
BEGIN
    UPDATE dtr_system.todtr d
    JOIN sdo_atlas.pass_slips s
      ON d.source_table = 'pass_slips'
     AND d.source_id = s.id
       SET d.employee_no = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = s.user_id LIMIT 1),
           d.full_name = s.employee_name,
           d.travel_type = 'Pass Slip',
           d.date_filed = COALESCE(s.request_date, s.date, DATE(s.created_at)),
           d.departure_date = COALESCE(s.date, DATE(s.created_at)),
           d.arrival_date = COALESCE(s.date, DATE(s.created_at)),
           d.departure_time = COALESCE(s.idt, '00:00:00'),
           d.arrival_time = COALESCE(s.iat, '00:00:00'),
           d.updated_at = NOW()
     WHERE s.status = 'approved';

    INSERT INTO dtr_system.todtr (
        employee_no,
        full_name,
        travel_type,
        date_filed,
        departure_date,
        arrival_date,
        departure_time,
        arrival_time,
        source_table,
        source_id,
        created_at,
        updated_at
    )
    SELECT
        (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = s.user_id LIMIT 1),
        s.employee_name,
        'Pass Slip',
        COALESCE(s.request_date, s.date, DATE(s.created_at)),
        COALESCE(s.date, DATE(s.created_at)),
        COALESCE(s.date, DATE(s.created_at)),
        COALESCE(s.idt, '00:00:00'),
        COALESCE(s.iat, '00:00:00'),
        'pass_slips',
        s.id,
        NOW(),
        NOW()
    FROM sdo_atlas.pass_slips s
    LEFT JOIN dtr_system.todtr d
      ON d.source_table = 'pass_slips'
     AND d.source_id = s.id
    WHERE s.status = 'approved'
      AND d.source_id IS NULL;

    DELETE d
    FROM dtr_system.todtr d
    LEFT JOIN sdo_atlas.pass_slips s
      ON d.source_table = 'pass_slips'
     AND d.source_id = s.id
    WHERE d.source_table = 'pass_slips'
      AND (s.id IS NULL OR s.status <> 'approved');
END$$

DELIMITER ;

-- One-time backfill for approved Locator Slips
INSERT INTO dtr_system.todtr (
    employee_no,
    full_name,
    travel_type,
    date_filed,
    departure_date,
    arrival_date,
    departure_time,
    arrival_time,
    source_table,
    source_id,
    created_at,
    updated_at
)
SELECT
    (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = s.user_id LIMIT 1),
    s.employee_name,
    'Locator Slip',
    COALESCE(s.date_filed, s.request_date, DATE(s.created_at)),
    DATE(COALESCE(s.date_time, s.created_at)),
    DATE(COALESCE(s.date_time, s.created_at)),
    TIME(COALESCE(s.date_time, s.created_at)),
    TIME(COALESCE(s.date_time, s.created_at)),
    'locator_slips',
    s.id,
    NOW(),
    NOW()
FROM sdo_atlas.locator_slips s
LEFT JOIN dtr_system.todtr d
  ON d.source_table = 'locator_slips'
 AND d.source_id = s.id
WHERE s.status = 'approved'
  AND d.source_id IS NULL;

-- One-time backfill for approved Pass Slips
INSERT INTO dtr_system.todtr (
    employee_no,
    full_name,
    travel_type,
    date_filed,
    departure_date,
    arrival_date,
    departure_time,
    arrival_time,
    source_table,
    source_id,
    created_at,
    updated_at
)
SELECT
    (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = s.user_id LIMIT 1),
    s.employee_name,
    'Pass Slip',
    COALESCE(s.request_date, s.date, DATE(s.created_at)),
    COALESCE(s.date, DATE(s.created_at)),
    COALESCE(s.date, DATE(s.created_at)),
    COALESCE(s.idt, '00:00:00'),
    COALESCE(s.iat, '00:00:00'),
    'pass_slips',
    s.id,
    NOW(),
    NOW()
FROM sdo_atlas.pass_slips s
LEFT JOIN dtr_system.todtr d
  ON d.source_table = 'pass_slips'
 AND d.source_id = s.id
WHERE s.status = 'approved'
  AND d.source_id IS NULL;
