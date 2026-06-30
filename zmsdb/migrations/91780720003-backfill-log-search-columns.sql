-- Backfill indexed search columns from the legacy JSON `data` blob.
-- Idempotent: only updates rows that have not been backfilled yet (client_name IS NULL).
-- Batched via stored procedure: each UPDATE commits separately (short row locks, no downtime).
-- Batch count = CEILING(pending / batch_size) + buffer for rows written during the migration.

DELIMITER $$

DROP PROCEDURE IF EXISTS zms_backfill_log_search_columns$$

CREATE PROCEDURE zms_backfill_log_search_columns(
    IN p_batch_size INT UNSIGNED,
    IN p_buffer_batches INT UNSIGNED
)
proc: BEGIN
    DECLARE v_pending INT UNSIGNED DEFAULT 0;
    DECLARE v_max_batches INT UNSIGNED DEFAULT 1;
    DECLARE v_batch INT UNSIGNED DEFAULT 0;
    DECLARE v_affected INT DEFAULT 1;

    IF p_batch_size IS NULL OR p_batch_size < 1 THEN
        SET p_batch_size = 50000;
    END IF;

    IF p_buffer_batches IS NULL THEN
        SET p_buffer_batches = 20;
    END IF;

    SELECT COUNT(*) INTO v_pending
    FROM `log`
    WHERE `type` = 'buerger'
      AND `data` IS NOT NULL
      AND `data` != ''
      AND `client_name` IS NULL;

    SET v_max_batches = CEILING(v_pending / p_batch_size) + p_buffer_batches;

    WHILE v_batch < v_max_batches DO
        UPDATE `log`
        SET
            `action` = CASE JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Aktion'))
                WHEN 'E-Mail-Versand erfolgreich' THEN 'mail_success'
                WHEN 'E-Mail-Versand ist fehlgeschlagen' THEN 'mail_fail'
                WHEN 'Terminstatus wurde geändert' THEN 'status_changed'
                WHEN 'Erinnerungsmail wurde gesendet' THEN 'reminder_sent'
                WHEN 'Termin aus der Warteschlange entfernt' THEN 'removed_from_queue'
                WHEN 'Termin wurde aufgerufen' THEN 'called'
                WHEN 'Termin wurde archiviert' THEN 'archived'
                WHEN 'Termin wurde geändert' THEN 'edited'
                WHEN 'Termin wurde weitergeleitet' THEN 'redirected'
                WHEN 'Neuer Termin wurde erstellt' THEN 'created'
                WHEN 'Termin wurde gelöscht' THEN 'deleted'
                WHEN 'Termin wurde abgesagt' THEN 'canceled'
                ELSE NULL
            END,
            `display_number` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Terminnummer')), ''),
            `queue_number` = CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Wartenummer')), '') AS UNSIGNED),
            `appointment_at` = STR_TO_DATE(
                NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Terminzeit')), ''),
                '%d.%m.%Y %H:%i:%s'
            ),
            `slot_count` = CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Slots')), '') AS UNSIGNED),
            `client_name` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$."Bürger*in"')), ''),
            `services` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Dienstleistungen')), ''),
            `scope_name` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Standort')), ''),
            `client_email` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.E-Mail')), ''),
            `client_phone` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Telefon')), ''),
            `process_status` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Status')), ''),
            `db_status` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$."DB Status"')), '')
        WHERE `type` = 'buerger'
          AND `data` IS NOT NULL
          AND `data` != ''
          AND `client_name` IS NULL
        ORDER BY `log_id` ASC
        LIMIT p_batch_size;

        SET v_affected = ROW_COUNT();
        SET v_batch = v_batch + 1;

        IF v_affected = 0 THEN
            LEAVE proc;
        END IF;
    END WHILE;
END$$

DELIMITER ;

CALL zms_backfill_log_search_columns(50000, 20);

DROP PROCEDURE IF EXISTS zms_backfill_log_search_columns;
