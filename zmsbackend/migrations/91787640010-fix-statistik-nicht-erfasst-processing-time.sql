-- ZMSKVR-1573: Copy processing time onto "nicht erfasst" statistik rows.
--
-- archiveStatisticData wrote anliegenid = -1 when buergeranliegen had no row
-- ("ohne Erfassung") but left processing_time NULL/0. The live report Ø and
-- Summe are calculated from statistik at read time (AVG + weighted mean), so
-- only this column needs updating.
--
-- Source of truth: buergerarchiv.processing_time for that lastbuergerarchivid.
-- True "ohne Erfassung" still has no buergeranliegen row (AnliegenID is unsigned
-- and cannot store -1). "Nicht erbracht" (AnliegenID 0) is left alone.
--
-- Idempotent: only rows that are still anliegenid = -1 with empty processing_time.
-- Batched via stored procedure: each UPDATE commits separately (short row locks).

DELIMITER $$

DROP PROCEDURE IF EXISTS zms_fix_statistik_nicht_erfasst_processing_time$$

CREATE PROCEDURE zms_fix_statistik_nicht_erfasst_processing_time(
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
    FROM statistik s
    INNER JOIN buergerarchiv ba
        ON ba.BuergerarchivID = s.lastbuergerarchivid
    LEFT JOIN buergeranliegen ban
        ON ban.BuergerarchivID = s.lastbuergerarchivid
    WHERE s.anliegenid = -1
      AND ban.BuergeranliegenID IS NULL
      AND (s.processing_time IS NULL OR s.processing_time = 0)
      AND ba.processing_time > 0;

    SET v_max_batches = CEILING(v_pending / p_batch_size) + p_buffer_batches;

    WHILE v_batch < v_max_batches DO
        UPDATE statistik s
        INNER JOIN (
            SELECT s2.statistikid
            FROM statistik s2
            INNER JOIN buergerarchiv ba
                ON ba.BuergerarchivID = s2.lastbuergerarchivid
            LEFT JOIN buergeranliegen ban
                ON ban.BuergerarchivID = s2.lastbuergerarchivid
            WHERE s2.anliegenid = -1
              AND ban.BuergeranliegenID IS NULL
              AND (s2.processing_time IS NULL OR s2.processing_time = 0)
              AND ba.processing_time > 0
            ORDER BY s2.statistikid ASC
            LIMIT p_batch_size
        ) batch ON batch.statistikid = s.statistikid
        INNER JOIN buergerarchiv ba
            ON ba.BuergerarchivID = s.lastbuergerarchivid
        SET s.processing_time = ba.processing_time;

        SET v_batch = v_batch + 1;

        SELECT COUNT(*) INTO v_pending
        FROM statistik s
        INNER JOIN buergerarchiv ba
            ON ba.BuergerarchivID = s.lastbuergerarchivid
        LEFT JOIN buergeranliegen ban
            ON ban.BuergerarchivID = s.lastbuergerarchivid
        WHERE s.anliegenid = -1
          AND ban.BuergeranliegenID IS NULL
          AND (s.processing_time IS NULL OR s.processing_time = 0)
          AND ba.processing_time > 0;

        IF v_pending = 0 THEN
            LEAVE proc;
        END IF;
    END WHILE;
END$$

DELIMITER ;

CALL zms_fix_statistik_nicht_erfasst_processing_time(50000, 20);

DROP PROCEDURE IF EXISTS zms_fix_statistik_nicht_erfasst_processing_time;
