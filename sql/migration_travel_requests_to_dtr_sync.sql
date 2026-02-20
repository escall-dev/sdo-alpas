-- Mirror SDO Alpas authority_to_travel requests to DTR system
-- Source DB: sdo_atlas
-- Target DB: dtr_system
-- Requires: INSERT/UPDATE on dtr_system.todtr and TRIGGER privilege on sdo_atlas

USE sdo_atlas;

-- Source table in this project: authority_to_travel (PK: id)

DROP TRIGGER IF EXISTS trg_authority_to_travel_ai_to_dtr;
DROP TRIGGER IF EXISTS trg_authority_to_travel_au_to_dtr;

DELIMITER $$

CREATE TRIGGER trg_authority_to_travel_ai_to_dtr
AFTER INSERT ON authority_to_travel
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE dtr_system.todtr
           SET employee_no = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
               full_name = NEW.employee_name,
               travel_type = 'Authority to Travel',
               date_filed = NEW.date_filed,
               departure_date = NEW.date_from,
               arrival_date = NEW.date_to,
               departure_time = NULL,
               arrival_time = NULL,
               updated_at = NOW()
         WHERE source_table = 'authority_to_travel'
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
                'Authority to Travel',
                NEW.date_filed,
                NEW.date_from,
                NEW.date_to,
                NULL,
                NULL,
                'authority_to_travel',
                NEW.id,
                NOW(),
                NOW()
            );
        END IF;
    END IF;
END$$

CREATE TRIGGER trg_authority_to_travel_au_to_dtr
AFTER UPDATE ON authority_to_travel
FOR EACH ROW
BEGIN
    IF NEW.status = 'approved' THEN
        UPDATE dtr_system.todtr
           SET employee_no = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = NEW.user_id LIMIT 1),
               full_name = NEW.employee_name,
               travel_type = 'Authority to Travel',
               date_filed = NEW.date_filed,
               departure_date = NEW.date_from,
               arrival_date = NEW.date_to,
               departure_time = NULL,
               arrival_time = NULL,
               updated_at = NOW()
         WHERE source_table = 'authority_to_travel'
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
                'Authority to Travel',
                NEW.date_filed,
                NEW.date_from,
                NEW.date_to,
                NULL,
                NULL,
                'authority_to_travel',
                NEW.id,
                NOW(),
                NOW()
            );
        END IF;
    ELSE
        DELETE FROM dtr_system.todtr
         WHERE source_table = 'authority_to_travel'
           AND source_id = NEW.id;
    END IF;
END$$

DELIMITER ;

-- Optional safety-net batch sync every 5 minutes.
-- Keep this enabled only if you want reconciliation in case trigger execution is interrupted.
-- Note: requires EVENT privilege and event_scheduler = ON.

DROP EVENT IF EXISTS ev_sync_authority_to_travel_to_dtr;

DELIMITER $$

CREATE EVENT ev_sync_authority_to_travel_to_dtr
ON SCHEDULE EVERY 5 MINUTE
DO
BEGIN
        UPDATE dtr_system.todtr d
        JOIN sdo_atlas.authority_to_travel s
            ON d.source_table = 'authority_to_travel'
     AND d.source_id = s.id
             SET d.employee_no = (SELECT au.employee_no FROM sdo_atlas.admin_users au WHERE au.id = s.user_id LIMIT 1),
                     d.full_name = s.employee_name,
           d.travel_type = 'Authority to Travel',
           d.date_filed = s.date_filed,
                     d.departure_date = s.date_from,
                     d.arrival_date = s.date_to,
                      d.departure_time = NULL,
                                    d.arrival_time = NULL,
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
        'Authority to Travel',
        s.date_filed,
                s.date_from,
                s.date_to,
                NULL,
                NULL,
                'authority_to_travel' AS source_table,
        s.id AS source_id,
        NOW() AS created_at,
        NOW() AS updated_at
        FROM sdo_atlas.authority_to_travel s
        LEFT JOIN dtr_system.todtr d
            ON d.source_table = 'authority_to_travel'
     AND d.source_id = s.id
        WHERE s.status = 'approved'
            AND d.source_id IS NULL;

        DELETE d
        FROM dtr_system.todtr d
        LEFT JOIN sdo_atlas.authority_to_travel s
            ON d.source_table = 'authority_to_travel'
         AND d.source_id = s.id
        WHERE d.source_table = 'authority_to_travel'
            AND (s.id IS NULL OR s.status <> 'approved');
END$$

DELIMITER ;

-- Enable event scheduler if needed (run once with sufficient privileges):
-- SET GLOBAL event_scheduler = ON;
