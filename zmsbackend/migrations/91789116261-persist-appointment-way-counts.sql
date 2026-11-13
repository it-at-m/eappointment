-- ZMSKVR-1626: Anzahl der Terminkunden speichern, die in die Wegezeit eingehen.
-- NULL bei älteren Zeilen: Auswertungen fallen dann auf die Wartenden-Anzahl zurück.
-- Wiederherstellung ab 2026-01-24, dieselbe Auswahl wie die Wegezeit.

ALTER TABLE wartenrstatistik
  ADD COLUMN hour_00_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_01_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_02_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_03_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_04_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_05_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_06_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_07_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_08_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_09_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_10_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_11_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_12_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_13_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_14_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_15_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_16_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_17_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_18_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_19_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_20_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_21_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_22_way_count_appointment INT UNSIGNED NULL DEFAULT NULL,
  ADD COLUMN hour_23_way_count_appointment INT UNSIGNED NULL DEFAULT NULL;

DELIMITER $$

DROP PROCEDURE IF EXISTS zms_restore_way_counts_zmskvr_1626$$

CREATE PROCEDURE zms_restore_way_counts_zmskvr_1626(
    IN p_start_date DATE,
    IN p_end_date DATE,
    IN p_batch_days INT UNSIGNED
)
proc: BEGIN
    DECLARE v_from DATE;
    DECLARE v_to DATE;
    DECLARE v_hour INT;

    IF p_start_date IS NULL THEN
        SET p_start_date = '2026-01-24';
    END IF;

    IF p_end_date IS NULL THEN
        SET p_end_date = CURDATE();
    END IF;

    IF p_batch_days IS NULL OR p_batch_days < 1 THEN
        SET p_batch_days = 7;
    END IF;

    IF p_start_date > p_end_date THEN
        LEAVE proc;
    END IF;

    SET v_from = p_start_date;

    WHILE v_from <= p_end_date DO
        SET v_to = LEAST(DATE_ADD(v_from, INTERVAL p_batch_days - 1 DAY), p_end_date);

        DROP TEMPORARY TABLE IF EXISTS tmp_ba_waycount;

        CREATE TEMPORARY TABLE tmp_ba_waycount ENGINE=Aria AS
        SELECT
          StandortID AS scope_id,
          Datum AS datum,
          HOUR(STR_TO_DATE(`Timestamp`, '%H:%i:%s')) AS bucket_hour,
          COUNT(*) AS way_count
        FROM buergerarchiv
        WHERE Datum >= v_from
          AND Datum <= v_to
          AND StandortID > 0
          AND mitTermin = 1
          AND (
            (waiting_time IS NOT NULL AND waiting_time > 0)
            OR (way_time IS NOT NULL AND COALESCE(nicht_erschienen, 0) = 0)
          )
        GROUP BY
          StandortID,
          Datum,
          HOUR(STR_TO_DATE(`Timestamp`, '%H:%i:%s'));

        SET v_hour = 0;
        WHILE v_hour <= 23 DO
          SET @hour_col = LPAD(v_hour, 2, '0');

          SET @sql = CONCAT(
            'UPDATE wartenrstatistik w ',
            'JOIN tmp_ba_waycount a ON a.scope_id = w.standortid AND a.datum = w.datum ',
            'AND a.bucket_hour = ', v_hour, ' ',
            'SET w.hour_', @hour_col, '_way_count_appointment = a.way_count'
          );
          PREPARE zms_1626_count FROM @sql;
          EXECUTE zms_1626_count;
          DEALLOCATE PREPARE zms_1626_count;

          SET v_hour = v_hour + 1;
        END WHILE;

        DROP TEMPORARY TABLE IF EXISTS tmp_ba_waycount;
        SET v_from = DATE_ADD(v_to, INTERVAL 1 DAY);
    END WHILE;
END$$

DELIMITER ;

CALL zms_restore_way_counts_zmskvr_1626('2026-01-24', CURDATE(), 7);

DROP PROCEDURE IF EXISTS zms_restore_way_counts_zmskvr_1626;
