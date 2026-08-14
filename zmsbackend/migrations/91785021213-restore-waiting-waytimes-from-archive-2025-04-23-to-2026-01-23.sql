-- ZMSKVR-1213: restore Wegezeiten in wartenrstatistik for 2025-04-23 .. 2026-01-23
-- Background:
--   From muc23 (~2025-04-23) until muc36 (2026-01-23), CalculateDailyWaitingStatisticByCron
--   divided archive way_time by 60 once too often (ZMSKVR-431). Values were stored as
--   hours:minutes instead of minutes:seconds in the waiting statistic.
-- Strategy (same recover pattern as 91770274084 / 91757926126, batched like log backfills):
--   Recalculate avg way times from buergerarchiv without /60, in date-window batches
--   so each UPDATE commits separately (short row locks).
--   Updates only hour_*_way_time_*; counts / waiting times stay untouched.
--   Uses current column names after 91775568666 / 91775568667.
-- Idempotent: re-running recalculates the same averages from buergerarchiv.

DELIMITER $$

DROP PROCEDURE IF EXISTS zms_restore_waiting_waytimes_zmskvr_1213$$

CREATE PROCEDURE zms_restore_waiting_waytimes_zmskvr_1213(
    IN p_start_date DATE,
    IN p_end_date DATE,
    IN p_batch_days INT UNSIGNED
)
proc: BEGIN
    DECLARE v_from DATE;
    DECLARE v_to DATE;

    IF p_start_date IS NULL THEN
        SET p_start_date = '2025-04-23';
    END IF;

    IF p_end_date IS NULL THEN
        SET p_end_date = '2026-01-23';
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

        DROP TEMPORARY TABLE IF EXISTS tmp_ba_raw;
        DROP TEMPORARY TABLE IF EXISTS tmp_ba_agg;
        DROP TEMPORARY TABLE IF EXISTS tmp_pivot;

        CREATE TEMPORARY TABLE tmp_ba_raw ENGINE=Aria AS
        SELECT
          StandortID AS scope_id,
          Datum      AS datum,
          HOUR(STR_TO_DATE(`Timestamp`, '%H:%i:%s')) AS bucket_hour,
          CASE WHEN mitTermin = 1 THEN 'termin' ELSE 'spontan' END AS type,
          COALESCE(way_time, 0) AS way_minutes
        FROM buergerarchiv
        WHERE Datum >= v_from
          AND Datum <= v_to
          AND (nicht_erschienen IS NULL OR nicht_erschienen = 0);

        CREATE TEMPORARY TABLE tmp_ba_agg ENGINE=Aria AS
        SELECT
          scope_id,
          datum,
          bucket_hour,
          type,
          ROUND(AVG(COALESCE(way_minutes, 0)), 2) AS avg_way_minutes
        FROM tmp_ba_raw
        GROUP BY scope_id, datum, bucket_hour, type;

        CREATE TEMPORARY TABLE tmp_pivot ENGINE=Aria AS
        SELECT
          scope_id,
          datum,

          /* avg way: spontaneous */
      MAX(CASE WHEN type='spontan' AND bucket_hour= 0 THEN avg_way_minutes END) AS hour_00_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour= 1 THEN avg_way_minutes END) AS hour_01_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour= 2 THEN avg_way_minutes END) AS hour_02_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour= 3 THEN avg_way_minutes END) AS hour_03_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour= 4 THEN avg_way_minutes END) AS hour_04_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour= 5 THEN avg_way_minutes END) AS hour_05_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour= 6 THEN avg_way_minutes END) AS hour_06_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour= 7 THEN avg_way_minutes END) AS hour_07_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour= 8 THEN avg_way_minutes END) AS hour_08_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour= 9 THEN avg_way_minutes END) AS hour_09_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=10 THEN avg_way_minutes END) AS hour_10_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=11 THEN avg_way_minutes END) AS hour_11_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=12 THEN avg_way_minutes END) AS hour_12_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=13 THEN avg_way_minutes END) AS hour_13_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=14 THEN avg_way_minutes END) AS hour_14_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=15 THEN avg_way_minutes END) AS hour_15_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=16 THEN avg_way_minutes END) AS hour_16_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=17 THEN avg_way_minutes END) AS hour_17_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=18 THEN avg_way_minutes END) AS hour_18_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=19 THEN avg_way_minutes END) AS hour_19_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=20 THEN avg_way_minutes END) AS hour_20_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=21 THEN avg_way_minutes END) AS hour_21_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=22 THEN avg_way_minutes END) AS hour_22_way_time_spontaneous,
      MAX(CASE WHEN type='spontan' AND bucket_hour=23 THEN avg_way_minutes END) AS hour_23_way_time_spontaneous,

          /* avg way: appointment */
      MAX(CASE WHEN type='termin' AND bucket_hour= 0 THEN avg_way_minutes END) AS hour_00_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour= 1 THEN avg_way_minutes END) AS hour_01_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour= 2 THEN avg_way_minutes END) AS hour_02_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour= 3 THEN avg_way_minutes END) AS hour_03_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour= 4 THEN avg_way_minutes END) AS hour_04_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour= 5 THEN avg_way_minutes END) AS hour_05_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour= 6 THEN avg_way_minutes END) AS hour_06_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour= 7 THEN avg_way_minutes END) AS hour_07_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour= 8 THEN avg_way_minutes END) AS hour_08_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour= 9 THEN avg_way_minutes END) AS hour_09_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=10 THEN avg_way_minutes END) AS hour_10_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=11 THEN avg_way_minutes END) AS hour_11_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=12 THEN avg_way_minutes END) AS hour_12_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=13 THEN avg_way_minutes END) AS hour_13_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=14 THEN avg_way_minutes END) AS hour_14_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=15 THEN avg_way_minutes END) AS hour_15_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=16 THEN avg_way_minutes END) AS hour_16_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=17 THEN avg_way_minutes END) AS hour_17_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=18 THEN avg_way_minutes END) AS hour_18_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=19 THEN avg_way_minutes END) AS hour_19_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=20 THEN avg_way_minutes END) AS hour_20_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=21 THEN avg_way_minutes END) AS hour_21_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=22 THEN avg_way_minutes END) AS hour_22_way_time_appointment,
      MAX(CASE WHEN type='termin' AND bucket_hour=23 THEN avg_way_minutes END) AS hour_23_way_time_appointment

        FROM tmp_ba_agg
        GROUP BY scope_id, datum;

        INSERT INTO wartenrstatistik (standortid, datum)
        SELECT scope_id, datum FROM tmp_pivot
        ON DUPLICATE KEY UPDATE datum = VALUES(datum);

        UPDATE wartenrstatistik w
        JOIN tmp_pivot p ON p.scope_id = w.standortid AND p.datum = w.datum
        SET
          /* avg way: spontaneous */
      w.hour_00_way_time_spontaneous = COALESCE(p.hour_00_way_time_spontaneous, 0),
      w.hour_01_way_time_spontaneous = COALESCE(p.hour_01_way_time_spontaneous, 0),
      w.hour_02_way_time_spontaneous = COALESCE(p.hour_02_way_time_spontaneous, 0),
      w.hour_03_way_time_spontaneous = COALESCE(p.hour_03_way_time_spontaneous, 0),
      w.hour_04_way_time_spontaneous = COALESCE(p.hour_04_way_time_spontaneous, 0),
      w.hour_05_way_time_spontaneous = COALESCE(p.hour_05_way_time_spontaneous, 0),
      w.hour_06_way_time_spontaneous = COALESCE(p.hour_06_way_time_spontaneous, 0),
      w.hour_07_way_time_spontaneous = COALESCE(p.hour_07_way_time_spontaneous, 0),
      w.hour_08_way_time_spontaneous = COALESCE(p.hour_08_way_time_spontaneous, 0),
      w.hour_09_way_time_spontaneous = COALESCE(p.hour_09_way_time_spontaneous, 0),
      w.hour_10_way_time_spontaneous = COALESCE(p.hour_10_way_time_spontaneous, 0),
      w.hour_11_way_time_spontaneous = COALESCE(p.hour_11_way_time_spontaneous, 0),
      w.hour_12_way_time_spontaneous = COALESCE(p.hour_12_way_time_spontaneous, 0),
      w.hour_13_way_time_spontaneous = COALESCE(p.hour_13_way_time_spontaneous, 0),
      w.hour_14_way_time_spontaneous = COALESCE(p.hour_14_way_time_spontaneous, 0),
      w.hour_15_way_time_spontaneous = COALESCE(p.hour_15_way_time_spontaneous, 0),
      w.hour_16_way_time_spontaneous = COALESCE(p.hour_16_way_time_spontaneous, 0),
      w.hour_17_way_time_spontaneous = COALESCE(p.hour_17_way_time_spontaneous, 0),
      w.hour_18_way_time_spontaneous = COALESCE(p.hour_18_way_time_spontaneous, 0),
      w.hour_19_way_time_spontaneous = COALESCE(p.hour_19_way_time_spontaneous, 0),
      w.hour_20_way_time_spontaneous = COALESCE(p.hour_20_way_time_spontaneous, 0),
      w.hour_21_way_time_spontaneous = COALESCE(p.hour_21_way_time_spontaneous, 0),
      w.hour_22_way_time_spontaneous = COALESCE(p.hour_22_way_time_spontaneous, 0),
      w.hour_23_way_time_spontaneous = COALESCE(p.hour_23_way_time_spontaneous, 0),

          /* avg way: appointment */
      w.hour_00_way_time_appointment = COALESCE(p.hour_00_way_time_appointment, 0),
      w.hour_01_way_time_appointment = COALESCE(p.hour_01_way_time_appointment, 0),
      w.hour_02_way_time_appointment = COALESCE(p.hour_02_way_time_appointment, 0),
      w.hour_03_way_time_appointment = COALESCE(p.hour_03_way_time_appointment, 0),
      w.hour_04_way_time_appointment = COALESCE(p.hour_04_way_time_appointment, 0),
      w.hour_05_way_time_appointment = COALESCE(p.hour_05_way_time_appointment, 0),
      w.hour_06_way_time_appointment = COALESCE(p.hour_06_way_time_appointment, 0),
      w.hour_07_way_time_appointment = COALESCE(p.hour_07_way_time_appointment, 0),
      w.hour_08_way_time_appointment = COALESCE(p.hour_08_way_time_appointment, 0),
      w.hour_09_way_time_appointment = COALESCE(p.hour_09_way_time_appointment, 0),
      w.hour_10_way_time_appointment = COALESCE(p.hour_10_way_time_appointment, 0),
      w.hour_11_way_time_appointment = COALESCE(p.hour_11_way_time_appointment, 0),
      w.hour_12_way_time_appointment = COALESCE(p.hour_12_way_time_appointment, 0),
      w.hour_13_way_time_appointment = COALESCE(p.hour_13_way_time_appointment, 0),
      w.hour_14_way_time_appointment = COALESCE(p.hour_14_way_time_appointment, 0),
      w.hour_15_way_time_appointment = COALESCE(p.hour_15_way_time_appointment, 0),
      w.hour_16_way_time_appointment = COALESCE(p.hour_16_way_time_appointment, 0),
      w.hour_17_way_time_appointment = COALESCE(p.hour_17_way_time_appointment, 0),
      w.hour_18_way_time_appointment = COALESCE(p.hour_18_way_time_appointment, 0),
      w.hour_19_way_time_appointment = COALESCE(p.hour_19_way_time_appointment, 0),
      w.hour_20_way_time_appointment = COALESCE(p.hour_20_way_time_appointment, 0),
      w.hour_21_way_time_appointment = COALESCE(p.hour_21_way_time_appointment, 0),
      w.hour_22_way_time_appointment = COALESCE(p.hour_22_way_time_appointment, 0),
      w.hour_23_way_time_appointment = COALESCE(p.hour_23_way_time_appointment, 0);

        DROP TEMPORARY TABLE IF EXISTS tmp_ba_raw;
        DROP TEMPORARY TABLE IF EXISTS tmp_ba_agg;
        DROP TEMPORARY TABLE IF EXISTS tmp_pivot;

        SET v_from = DATE_ADD(v_to, INTERVAL 1 DAY);
    END WHILE;
END$$

DELIMITER ;

CALL zms_restore_waiting_waytimes_zmskvr_1213('2025-04-23', '2026-01-23', 7);

DROP PROCEDURE IF EXISTS zms_restore_waiting_waytimes_zmskvr_1213;
