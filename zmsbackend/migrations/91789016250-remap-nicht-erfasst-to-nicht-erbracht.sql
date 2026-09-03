-- ZMSKVR-1625: Map "Dienstleistung wurde nicht erfasst" onto
-- "Dienstleistung konnte nicht erbracht werden".
--
-- Source of truth: finished archives without a buergeranliegen row were
-- "ohne Erfassung" (statistik.anliegenid = -1). "Nicht erbracht" is
-- AnliegenID 0. After this, both are stored as 0.
--
-- Idempotent. Batched via stored procedures: each write commits separately
-- (short row locks), same pattern as 91785020010.

DELIMITER $$

DROP PROCEDURE IF EXISTS zms_backfill_nicht_erfasst_buergeranliegen$$

CREATE PROCEDURE zms_backfill_nicht_erfasst_buergeranliegen(
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
    FROM buergerarchiv ba
    WHERE COALESCE(ba.nicht_erschienen, 0) = 0
      AND NOT EXISTS (
          SELECT 1
          FROM buergeranliegen ban
          WHERE ban.BuergerarchivID = ba.BuergerarchivID
      );

    SET v_max_batches = CEILING(v_pending / p_batch_size) + p_buffer_batches;

    WHILE v_batch < v_max_batches DO
        INSERT INTO buergeranliegen (BuergerID, BuergerarchivID, AnliegenID, source)
        SELECT 0, ba.BuergerarchivID, 0, 'dldb'
        FROM buergerarchiv ba
        WHERE COALESCE(ba.nicht_erschienen, 0) = 0
          AND NOT EXISTS (
              SELECT 1
              FROM buergeranliegen ban
              WHERE ban.BuergerarchivID = ba.BuergerarchivID
          )
        LIMIT p_batch_size;

        SET v_batch = v_batch + 1;

        SELECT COUNT(*) INTO v_pending
        FROM buergerarchiv ba
        WHERE COALESCE(ba.nicht_erschienen, 0) = 0
          AND NOT EXISTS (
              SELECT 1
              FROM buergeranliegen ban
              WHERE ban.BuergerarchivID = ba.BuergerarchivID
          );

        IF v_pending = 0 THEN
            LEAVE proc;
        END IF;
    END WHILE;
END$$

DROP PROCEDURE IF EXISTS zms_remap_statistik_nicht_erfasst_anliegenid$$

CREATE PROCEDURE zms_remap_statistik_nicht_erfasst_anliegenid(
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
    FROM statistik
    WHERE anliegenid = -1;

    SET v_max_batches = CEILING(v_pending / p_batch_size) + p_buffer_batches;

    WHILE v_batch < v_max_batches DO
        UPDATE statistik
        SET anliegenid = 0
        WHERE anliegenid = -1
        ORDER BY statistikid ASC
        LIMIT p_batch_size;

        SET v_batch = v_batch + 1;

        SELECT COUNT(*) INTO v_pending
        FROM statistik
        WHERE anliegenid = -1;

        IF v_pending = 0 THEN
            LEAVE proc;
        END IF;
    END WHILE;
END$$

DELIMITER ;

CALL zms_backfill_nicht_erfasst_buergeranliegen(50000, 20);
CALL zms_remap_statistik_nicht_erfasst_anliegenid(50000, 20);

DROP PROCEDURE IF EXISTS zms_backfill_nicht_erfasst_buergeranliegen;
DROP PROCEDURE IF EXISTS zms_remap_statistik_nicht_erfasst_anliegenid;
