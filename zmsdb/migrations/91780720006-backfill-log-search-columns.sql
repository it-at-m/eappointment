-- Backfill indexed search columns from the legacy JSON `data` blob.
-- Runs AFTER 91780720002 (columns) and 91780720003/04/05 (indexes).
-- Indexes are created on empty columns first (fast), then this migration populates them in batches.
--
-- Idempotent: only updates rows that have not been backfilled yet (citizen_name IS NULL).
-- Batched via stored procedure: each UPDATE commits separately (short row locks).
-- Batch count = CEILING(pending / batch_size) + buffer for rows written during the migration.
-- Legacy JSON search in `data` remains available until backfill completes.
--
-- Skips stuck rows that cannot yield a citizen_name (action set, no Bürger*in in JSON).
-- Exits only when no eligible rows remain (not on ROW_COUNT() = 0 alone).

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
      AND `citizen_name` IS NULL
      AND NOT (
          `action` IS NOT NULL
          AND (
              JSON_EXTRACT(`data`, '$."Bürger*in"') IS NULL
              OR JSON_UNQUOTE(JSON_EXTRACT(`data`, '$."Bürger*in"')) = ''
          )
      );

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
            `citizen_name` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$."Bürger*in"')), ''),
            `services` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Dienstleistungen')), ''),
            `scope_name` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Standort')), ''),
            `citizen_email` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.E-Mail')), ''),
            `citizen_phone` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Telefon')), ''),
            `process_status` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Status')), ''),
            `db_status` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$."DB Status"')), '')
        WHERE `type` = 'buerger'
          AND `data` IS NOT NULL
          AND `data` != ''
          AND `citizen_name` IS NULL
          AND NOT (
              `action` IS NOT NULL
              AND (
                  JSON_EXTRACT(`data`, '$."Bürger*in"') IS NULL
                  OR JSON_UNQUOTE(JSON_EXTRACT(`data`, '$."Bürger*in"')) = ''
              )
          )
        ORDER BY `log_id` ASC
        LIMIT p_batch_size;

        SET v_affected = ROW_COUNT();
        SET v_batch = v_batch + 1;

        SELECT COUNT(*) INTO v_pending
        FROM `log`
        WHERE `type` = 'buerger'
          AND `data` IS NOT NULL
          AND `data` != ''
          AND `citizen_name` IS NULL
          AND NOT (
              `action` IS NOT NULL
              AND (
                  JSON_EXTRACT(`data`, '$."Bürger*in"') IS NULL
                  OR JSON_UNQUOTE(JSON_EXTRACT(`data`, '$."Bürger*in"')) = ''
              )
          );

        IF v_pending = 0 THEN
            LEAVE proc;
        END IF;
    END WHILE;
END$$

DELIMITER ;

CALL zms_backfill_log_search_columns(50000, 20);

DROP PROCEDURE IF EXISTS zms_backfill_log_search_columns;
