-- Restore way time (wegezeit) statistics for 2026-02-04 only
-- Strategy:
-- 1) Delete existing way time stats for the specific date
-- 2) Aggregate way time from buergerarchiv for 2026-02-04
-- 3) Pivot per (scope, date) into the wartenrstatistik schema (only wegezeit columns)

SET @start_date := '2026-02-04';

-- 0) Safety: temp tables cleanup
DROP TEMPORARY TABLE IF EXISTS tmp_ba_raw;
DROP TEMPORARY TABLE IF EXISTS tmp_ba_agg;
DROP TEMPORARY TABLE IF EXISTS tmp_pivot;

-- 1) Note: We only update way time columns, so no deletion needed
--    The UPDATE statement below will only modify wegezeit_ab_XX columns

-- 2) Pull relevant archive rows for the date range
--    We approximate cron logic:
--    - exclude missed (nicht_erschienen)
--    - exclude rows with wegezeit = 0 or NULL
--    - use mitTermin to distinguish buckets
--    - map Timestamp hour to buckets
--    - round similar to cron
CREATE TEMPORARY TABLE tmp_ba_raw ENGINE=Aria AS
SELECT
  StandortID AS scope_id,
  Datum      AS datum,
  HOUR(STR_TO_DATE(`Timestamp`, '%H:%i:%s')) AS bucket_hour,
  CASE WHEN mitTermin = 1 THEN 'termin' ELSE 'spontan' END AS type,
  CASE WHEN wartezeit = 0 THEN NULL ELSE ROUND(wartezeit, 2) END AS waited_minutes,
  COALESCE(wegezeit, 0) AS way_minutes
FROM buergerarchiv
WHERE Datum = @start_date
  AND (nicht_erschienen IS NULL OR nicht_erschienen = 0)
  AND wegezeit IS NOT NULL
  AND wegezeit > 0;

-- 3) Aggregate per scope/date/hour/type
CREATE TEMPORARY TABLE tmp_ba_agg ENGINE=Aria AS
SELECT
  scope_id,
  datum,
  bucket_hour,
  type,
  COUNT(*)                                AS cnt,
  ROUND(AVG(waited_minutes), 2)           AS avg_wait_minutes,
  ROUND(AVG(COALESCE(way_minutes, 0)), 2) AS avg_way_minutes
FROM tmp_ba_raw
GROUP BY scope_id, datum, bucket_hour, type;

-- 4) Pivot to one row per scope/date with only way time (wegezeit) columns
CREATE TEMPORARY TABLE tmp_pivot ENGINE=Aria AS
SELECT
  scope_id,
  datum,

  /* avg way: spontan */
  MAX(CASE WHEN type='spontan' AND bucket_hour= 0 THEN avg_way_minutes END) AS wegezeit_ab_00_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 1 THEN avg_way_minutes END) AS wegezeit_ab_01_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 2 THEN avg_way_minutes END) AS wegezeit_ab_02_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 3 THEN avg_way_minutes END) AS wegezeit_ab_03_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 4 THEN avg_way_minutes END) AS wegezeit_ab_04_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 5 THEN avg_way_minutes END) AS wegezeit_ab_05_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 6 THEN avg_way_minutes END) AS wegezeit_ab_06_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 7 THEN avg_way_minutes END) AS wegezeit_ab_07_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 8 THEN avg_way_minutes END) AS wegezeit_ab_08_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 9 THEN avg_way_minutes END) AS wegezeit_ab_09_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=10 THEN avg_way_minutes END) AS wegezeit_ab_10_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=11 THEN avg_way_minutes END) AS wegezeit_ab_11_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=12 THEN avg_way_minutes END) AS wegezeit_ab_12_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=13 THEN avg_way_minutes END) AS wegezeit_ab_13_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=14 THEN avg_way_minutes END) AS wegezeit_ab_14_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=15 THEN avg_way_minutes END) AS wegezeit_ab_15_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=16 THEN avg_way_minutes END) AS wegezeit_ab_16_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=17 THEN avg_way_minutes END) AS wegezeit_ab_17_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=18 THEN avg_way_minutes END) AS wegezeit_ab_18_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=19 THEN avg_way_minutes END) AS wegezeit_ab_19_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=20 THEN avg_way_minutes END) AS wegezeit_ab_20_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=21 THEN avg_way_minutes END) AS wegezeit_ab_21_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=22 THEN avg_way_minutes END) AS wegezeit_ab_22_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=23 THEN avg_way_minutes END) AS wegezeit_ab_23_spontan,

  /* avg way: termin */
  MAX(CASE WHEN type='termin'  AND bucket_hour= 0 THEN avg_way_minutes END) AS wegezeit_ab_00_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 1 THEN avg_way_minutes END) AS wegezeit_ab_01_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 2 THEN avg_way_minutes END) AS wegezeit_ab_02_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 3 THEN avg_way_minutes END) AS wegezeit_ab_03_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 4 THEN avg_way_minutes END) AS wegezeit_ab_04_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 5 THEN avg_way_minutes END) AS wegezeit_ab_05_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 6 THEN avg_way_minutes END) AS wegezeit_ab_06_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 7 THEN avg_way_minutes END) AS wegezeit_ab_07_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 8 THEN avg_way_minutes END) AS wegezeit_ab_08_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 9 THEN avg_way_minutes END) AS wegezeit_ab_09_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=10 THEN avg_way_minutes END) AS wegezeit_ab_10_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=11 THEN avg_way_minutes END) AS wegezeit_ab_11_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=12 THEN avg_way_minutes END) AS wegezeit_ab_12_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=13 THEN avg_way_minutes END) AS wegezeit_ab_13_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=14 THEN avg_way_minutes END) AS wegezeit_ab_14_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=15 THEN avg_way_minutes END) AS wegezeit_ab_15_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=16 THEN avg_way_minutes END) AS wegezeit_ab_16_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=17 THEN avg_way_minutes END) AS wegezeit_ab_17_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=18 THEN avg_way_minutes END) AS wegezeit_ab_18_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=19 THEN avg_way_minutes END) AS wegezeit_ab_19_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=20 THEN avg_way_minutes END) AS wegezeit_ab_20_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=21 THEN avg_way_minutes END) AS wegezeit_ab_21_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=22 THEN avg_way_minutes END) AS wegezeit_ab_22_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=23 THEN avg_way_minutes END) AS wegezeit_ab_23_termin

FROM tmp_ba_agg
GROUP BY scope_id, datum;

-- 5) Insert and update wartenrstatistik for scope/date pairs (only way time columns)
INSERT INTO wartenrstatistik (standortid, datum)
SELECT scope_id, datum FROM tmp_pivot
ON DUPLICATE KEY UPDATE datum = VALUES(datum);

UPDATE wartenrstatistik w
JOIN tmp_pivot p ON p.scope_id = w.standortid AND p.datum = w.datum
SET
  /* avg way: spontan */
  w.wegezeit_ab_00_spontan = COALESCE(p.wegezeit_ab_00_spontan, 0),
  w.wegezeit_ab_01_spontan = COALESCE(p.wegezeit_ab_01_spontan, 0),
  w.wegezeit_ab_02_spontan = COALESCE(p.wegezeit_ab_02_spontan, 0),
  w.wegezeit_ab_03_spontan = COALESCE(p.wegezeit_ab_03_spontan, 0),
  w.wegezeit_ab_04_spontan = COALESCE(p.wegezeit_ab_04_spontan, 0),
  w.wegezeit_ab_05_spontan = COALESCE(p.wegezeit_ab_05_spontan, 0),
  w.wegezeit_ab_06_spontan = COALESCE(p.wegezeit_ab_06_spontan, 0),
  w.wegezeit_ab_07_spontan = COALESCE(p.wegezeit_ab_07_spontan, 0),
  w.wegezeit_ab_08_spontan = COALESCE(p.wegezeit_ab_08_spontan, 0),
  w.wegezeit_ab_09_spontan = COALESCE(p.wegezeit_ab_09_spontan, 0),
  w.wegezeit_ab_10_spontan = COALESCE(p.wegezeit_ab_10_spontan, 0),
  w.wegezeit_ab_11_spontan = COALESCE(p.wegezeit_ab_11_spontan, 0),
  w.wegezeit_ab_12_spontan = COALESCE(p.wegezeit_ab_12_spontan, 0),
  w.wegezeit_ab_13_spontan = COALESCE(p.wegezeit_ab_13_spontan, 0),
  w.wegezeit_ab_14_spontan = COALESCE(p.wegezeit_ab_14_spontan, 0),
  w.wegezeit_ab_15_spontan = COALESCE(p.wegezeit_ab_15_spontan, 0),
  w.wegezeit_ab_16_spontan = COALESCE(p.wegezeit_ab_16_spontan, 0),
  w.wegezeit_ab_17_spontan = COALESCE(p.wegezeit_ab_17_spontan, 0),
  w.wegezeit_ab_18_spontan = COALESCE(p.wegezeit_ab_18_spontan, 0),
  w.wegezeit_ab_19_spontan = COALESCE(p.wegezeit_ab_19_spontan, 0),
  w.wegezeit_ab_20_spontan = COALESCE(p.wegezeit_ab_20_spontan, 0),
  w.wegezeit_ab_21_spontan = COALESCE(p.wegezeit_ab_21_spontan, 0),
  w.wegezeit_ab_22_spontan = COALESCE(p.wegezeit_ab_22_spontan, 0),
  w.wegezeit_ab_23_spontan = COALESCE(p.wegezeit_ab_23_spontan, 0),

  /* avg way: termin */
  w.wegezeit_ab_00_termin = COALESCE(p.wegezeit_ab_00_termin, 0),
  w.wegezeit_ab_01_termin = COALESCE(p.wegezeit_ab_01_termin, 0),
  w.wegezeit_ab_02_termin = COALESCE(p.wegezeit_ab_02_termin, 0),
  w.wegezeit_ab_03_termin = COALESCE(p.wegezeit_ab_03_termin, 0),
  w.wegezeit_ab_04_termin = COALESCE(p.wegezeit_ab_04_termin, 0),
  w.wegezeit_ab_05_termin = COALESCE(p.wegezeit_ab_05_termin, 0),
  w.wegezeit_ab_06_termin = COALESCE(p.wegezeit_ab_06_termin, 0),
  w.wegezeit_ab_07_termin = COALESCE(p.wegezeit_ab_07_termin, 0),
  w.wegezeit_ab_08_termin = COALESCE(p.wegezeit_ab_08_termin, 0),
  w.wegezeit_ab_09_termin = COALESCE(p.wegezeit_ab_09_termin, 0),
  w.wegezeit_ab_10_termin = COALESCE(p.wegezeit_ab_10_termin, 0),
  w.wegezeit_ab_11_termin = COALESCE(p.wegezeit_ab_11_termin, 0),
  w.wegezeit_ab_12_termin = COALESCE(p.wegezeit_ab_12_termin, 0),
  w.wegezeit_ab_13_termin = COALESCE(p.wegezeit_ab_13_termin, 0),
  w.wegezeit_ab_14_termin = COALESCE(p.wegezeit_ab_14_termin, 0),
  w.wegezeit_ab_15_termin = COALESCE(p.wegezeit_ab_15_termin, 0),
  w.wegezeit_ab_16_termin = COALESCE(p.wegezeit_ab_16_termin, 0),
  w.wegezeit_ab_17_termin = COALESCE(p.wegezeit_ab_17_termin, 0),
  w.wegezeit_ab_18_termin = COALESCE(p.wegezeit_ab_18_termin, 0),
  w.wegezeit_ab_19_termin = COALESCE(p.wegezeit_ab_19_termin, 0),
  w.wegezeit_ab_20_termin = COALESCE(p.wegezeit_ab_20_termin, 0),
  w.wegezeit_ab_21_termin = COALESCE(p.wegezeit_ab_21_termin, 0),
  w.wegezeit_ab_22_termin = COALESCE(p.wegezeit_ab_22_termin, 0),
  w.wegezeit_ab_23_termin = COALESCE(p.wegezeit_ab_23_termin, 0);

-- Optional: cleanup temp tables for long-running sessions
DROP TEMPORARY TABLE IF EXISTS tmp_ba_raw;
DROP TEMPORARY TABLE IF EXISTS tmp_ba_agg;
DROP TEMPORARY TABLE IF EXISTS tmp_pivot;
