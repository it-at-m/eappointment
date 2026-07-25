-- Expand phase: add process_amendment column and backfill from legacy JSON `data`.
-- Deploy before code that stops writing `data` and before the contract migration.
--
-- Idempotent: only updates rows that have not been backfilled yet (process_amendment IS NULL).
-- Batched via stored procedure: each UPDATE commits separately (short row locks).
-- Batch count = CEILING(pending / batch_size) + buffer for rows written during the migration.

ALTER TABLE `log`
    ADD COLUMN IF NOT EXISTS `process_amendment` TEXT DEFAULT NULL;

DELIMITER $$

DROP PROCEDURE IF EXISTS zms_backfill_log_process_amendment$$

CREATE PROCEDURE zms_backfill_log_process_amendment(
    IN p_batch_size INT UNSIGNED,
    IN p_buffer_batches INT UNSIGNED
)
proc: BEGIN
    DECLARE v_pending INT UNSIGNED DEFAULT 0;
    DECLARE v_max_batches INT UNSIGNED DEFAULT 1;
    DECLARE v_batch INT UNSIGNED DEFAULT 0;

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
      AND `process_amendment` IS NULL
      AND JSON_EXTRACT(`data`, '$.Anmerkung') IS NOT NULL
      AND JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Anmerkung')) != '';

    SET v_max_batches = CEILING(v_pending / p_batch_size) + p_buffer_batches;

    WHILE v_batch < v_max_batches DO
        UPDATE `log`
        SET `process_amendment` = NULLIF(TRIM(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Anmerkung'))), '')
        WHERE `type` = 'buerger'
          AND `data` IS NOT NULL
          AND `data` != ''
          AND `process_amendment` IS NULL
          AND JSON_EXTRACT(`data`, '$.Anmerkung') IS NOT NULL
          AND JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Anmerkung')) != ''
        ORDER BY `log_id` ASC
        LIMIT p_batch_size;

        SET v_batch = v_batch + 1;

        SELECT COUNT(*) INTO v_pending
        FROM `log`
        WHERE `type` = 'buerger'
          AND `data` IS NOT NULL
          AND `data` != ''
          AND `process_amendment` IS NULL
          AND JSON_EXTRACT(`data`, '$.Anmerkung') IS NOT NULL
          AND JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Anmerkung')) != '';

        IF v_pending = 0 THEN
            LEAVE proc;
        END IF;
    END WHILE;
END$$

DELIMITER ;

CALL zms_backfill_log_process_amendment(50000, 20);

DROP PROCEDURE IF EXISTS zms_backfill_log_process_amendment;
