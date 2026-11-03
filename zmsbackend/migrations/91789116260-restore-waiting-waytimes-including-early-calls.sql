-- ZMSKVR-1626: Terminkunden mit Wartezeit 0 (Aufruf vor dem Termin) in die Wegezeit nehmen.
-- Anzahl und Wartezeit bleiben unverändert.
-- Fenster ab 2026-01-24, dem Tag nach der Wegezeit-Wiederherstellung 91785021213.
-- Dieselbe Auswahl wie CalculateDailyWaitingStatisticByCron: Wartezeit > 0,
-- plus erschienene Termine mit gespeicherter Wegezeit.

DELIMITER $$

DROP PROCEDURE IF EXISTS zms_restore_waiting_waytimes_zmskvr_1626$$

CREATE PROCEDURE zms_restore_waiting_waytimes_zmskvr_1626(
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

        DROP TEMPORARY TABLE IF EXISTS tmp_ba_agg;

        CREATE TEMPORARY TABLE tmp_ba_agg ENGINE=Aria AS
        SELECT
          StandortID AS scope_id,
          Datum AS datum,
          HOUR(STR_TO_DATE(`Timestamp`, '%H:%i:%s')) AS bucket_hour,
          CASE WHEN mitTermin = 1 THEN 'termin' ELSE 'spontan' END AS type,
          ROUND(AVG(COALESCE(way_time, 0)), 2) AS avg_way_minutes
        FROM buergerarchiv
        WHERE Datum >= v_from
          AND Datum <= v_to
          AND StandortID > 0
          AND (
            (waiting_time IS NOT NULL AND waiting_time > 0)
            OR (mitTermin = 1 AND way_time IS NOT NULL AND COALESCE(nicht_erschienen, 0) = 0)
          )
        GROUP BY
          StandortID,
          Datum,
          HOUR(STR_TO_DATE(`Timestamp`, '%H:%i:%s')),
          CASE WHEN mitTermin = 1 THEN 'termin' ELSE 'spontan' END;

        INSERT INTO wartenrstatistik (standortid, datum)
        SELECT DISTINCT a.scope_id, a.datum
        FROM tmp_ba_agg a
        WHERE NOT EXISTS (
          SELECT 1
          FROM wartenrstatistik w
          WHERE w.standortid = a.scope_id
            AND w.datum = a.datum
        );

        SET v_hour = 0;
        WHILE v_hour <= 23 DO
          SET @hour_col = LPAD(v_hour, 2, '0');

          SET @sql = CONCAT(
            'UPDATE wartenrstatistik w ',
            'JOIN tmp_ba_agg a ON a.scope_id = w.standortid AND a.datum = w.datum ',
            'AND a.bucket_hour = ', v_hour, ' AND a.type = ''termin'' ',
            'SET w.hour_', @hour_col, '_way_time_appointment = a.avg_way_minutes'
          );
          PREPARE zms_1626_way FROM @sql;
          EXECUTE zms_1626_way;
          DEALLOCATE PREPARE zms_1626_way;

          SET @sql = CONCAT(
            'UPDATE wartenrstatistik w ',
            'JOIN tmp_ba_agg a ON a.scope_id = w.standortid AND a.datum = w.datum ',
            'AND a.bucket_hour = ', v_hour, ' AND a.type = ''spontan'' ',
            'SET w.hour_', @hour_col, '_way_time_spontaneous = a.avg_way_minutes'
          );
          PREPARE zms_1626_way FROM @sql;
          EXECUTE zms_1626_way;
          DEALLOCATE PREPARE zms_1626_way;

          SET v_hour = v_hour + 1;
        END WHILE;

        DROP TEMPORARY TABLE IF EXISTS tmp_ba_agg;
        SET v_from = DATE_ADD(v_to, INTERVAL 1 DAY);
    END WHILE;
END$$

DELIMITER ;

CALL zms_restore_waiting_waytimes_zmskvr_1626('2026-01-24', CURDATE(), 7);

DROP PROCEDURE IF EXISTS zms_restore_waiting_waytimes_zmskvr_1626;
