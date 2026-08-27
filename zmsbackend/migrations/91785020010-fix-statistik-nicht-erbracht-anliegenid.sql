-- ZMSKVR-1496: Fix statistik rows where "nicht erbracht" (AnliegenID 0) was
-- collapsed to "nicht erfasst" (anliegenid -1) by archiveStatisticData.
--
-- Source of truth: buergeranliegen.AnliegenID = 0 for that BuergerarchivID.
-- True "ohne Erfassung" has no matching buergeranliegen row and stays -1.
--
-- Idempotent: only updates statistik rows that are still anliegenid = -1.
-- Batched via stored procedure: each UPDATE commits separately (short row locks).

DELIMITER $$

DROP PROCEDURE IF EXISTS zms_fix_statistik_nicht_erbracht_anliegenid$$

CREATE PROCEDURE zms_fix_statistik_nicht_erbracht_anliegenid(
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
    INNER JOIN buergeranliegen ba
        ON ba.BuergerarchivID = s.lastbuergerarchivid
       AND ba.AnliegenID = 0
    WHERE s.anliegenid = -1;

    SET v_max_batches = CEILING(v_pending / p_batch_size) + p_buffer_batches;

    WHILE v_batch < v_max_batches DO
        UPDATE statistik s
        INNER JOIN (
            SELECT s2.statistikid
            FROM statistik s2
            INNER JOIN buergeranliegen ba
                ON ba.BuergerarchivID = s2.lastbuergerarchivid
               AND ba.AnliegenID = 0
            WHERE s2.anliegenid = -1
            ORDER BY s2.statistikid ASC
            LIMIT p_batch_size
        ) batch ON batch.statistikid = s.statistikid
        SET s.anliegenid = 0;

        SET v_batch = v_batch + 1;

        SELECT COUNT(*) INTO v_pending
        FROM statistik s
        INNER JOIN buergeranliegen ba
            ON ba.BuergerarchivID = s.lastbuergerarchivid
           AND ba.AnliegenID = 0
        WHERE s.anliegenid = -1;

        IF v_pending = 0 THEN
            LEAVE proc;
        END IF;
    END WHILE;
END$$

DELIMITER ;

CALL zms_fix_statistik_nicht_erbracht_anliegenid(50000, 20);

DROP PROCEDURE IF EXISTS zms_fix_statistik_nicht_erbracht_anliegenid;
