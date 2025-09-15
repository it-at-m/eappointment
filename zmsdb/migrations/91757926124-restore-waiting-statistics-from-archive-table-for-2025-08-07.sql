-- Set the target date
SET @target_date := '2025-08-07';

-- Cleanup
DROP TEMPORARY TABLE IF EXISTS tmp_ba_raw;
DROP TEMPORARY TABLE IF EXISTS tmp_ba_agg;
DROP TEMPORARY TABLE IF EXISTS tmp_pivot;

-- 1) Pull relevant archive rows for the date
CREATE TEMPORARY TABLE tmp_ba_raw AS
SELECT
  StandortID AS scope_id,
  Datum      AS datum,
  HOUR(STR_TO_DATE(`Timestamp`, '%H:%i:%s')) AS bucket_hour,
  CASE WHEN mitTermin = 1 THEN 'termin' ELSE 'spontan' END AS type,
  CASE WHEN wartezeit = 0 THEN NULL ELSE ROUND(wartezeit, 2) END AS waited_minutes,  -- exclude zeros from AVG
  ROUND(COALESCE(wegezeit, 0) / 60.0, 2) AS way_minutes      -- per-row rounding like cron
FROM buergerarchiv
WHERE Datum = @target_date
  AND (nicht_erschienen IS NULL OR nicht_erschienen = 0)
  AND NOT EXISTS (
    SELECT 1
    FROM wartenrstatistik w
    WHERE w.standortid = buergerarchiv.StandortID
      AND w.datum = @target_date
  );

-- 2) Aggregate per scope/date/hour/type
CREATE TEMPORARY TABLE tmp_ba_agg AS
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

-- 3) Pivot to one row per scope/date with all 24h columns (counts + avg wait + avg way)
CREATE TEMPORARY TABLE tmp_pivot AS
SELECT
  scope_id,
  datum,

  /* counts: spontan */
  SUM(CASE WHEN type='spontan' AND bucket_hour= 0 THEN cnt ELSE 0 END) AS wartende_ab_00_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour= 1 THEN cnt ELSE 0 END) AS wartende_ab_01_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour= 2 THEN cnt ELSE 0 END) AS wartende_ab_02_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour= 3 THEN cnt ELSE 0 END) AS wartende_ab_03_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour= 4 THEN cnt ELSE 0 END) AS wartende_ab_04_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour= 5 THEN cnt ELSE 0 END) AS wartende_ab_05_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour= 6 THEN cnt ELSE 0 END) AS wartende_ab_06_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour= 7 THEN cnt ELSE 0 END) AS wartende_ab_07_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour= 8 THEN cnt ELSE 0 END) AS wartende_ab_08_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour= 9 THEN cnt ELSE 0 END) AS wartende_ab_09_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=10 THEN cnt ELSE 0 END) AS wartende_ab_10_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=11 THEN cnt ELSE 0 END) AS wartende_ab_11_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=12 THEN cnt ELSE 0 END) AS wartende_ab_12_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=13 THEN cnt ELSE 0 END) AS wartende_ab_13_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=14 THEN cnt ELSE 0 END) AS wartende_ab_14_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=15 THEN cnt ELSE 0 END) AS wartende_ab_15_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=16 THEN cnt ELSE 0 END) AS wartende_ab_16_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=17 THEN cnt ELSE 0 END) AS wartende_ab_17_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=18 THEN cnt ELSE 0 END) AS wartende_ab_18_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=19 THEN cnt ELSE 0 END) AS wartende_ab_19_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=20 THEN cnt ELSE 0 END) AS wartende_ab_20_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=21 THEN cnt ELSE 0 END) AS wartende_ab_21_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=22 THEN cnt ELSE 0 END) AS wartende_ab_22_spontan,
  SUM(CASE WHEN type='spontan' AND bucket_hour=23 THEN cnt ELSE 0 END) AS wartende_ab_23_spontan,

  /* counts: termin */
  SUM(CASE WHEN type='termin'  AND bucket_hour= 0 THEN cnt ELSE 0 END) AS wartende_ab_00_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour= 1 THEN cnt ELSE 0 END) AS wartende_ab_01_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour= 2 THEN cnt ELSE 0 END) AS wartende_ab_02_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour= 3 THEN cnt ELSE 0 END) AS wartende_ab_03_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour= 4 THEN cnt ELSE 0 END) AS wartende_ab_04_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour= 5 THEN cnt ELSE 0 END) AS wartende_ab_05_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour= 6 THEN cnt ELSE 0 END) AS wartende_ab_06_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour= 7 THEN cnt ELSE 0 END) AS wartende_ab_07_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour= 8 THEN cnt ELSE 0 END) AS wartende_ab_08_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour= 9 THEN cnt ELSE 0 END) AS wartende_ab_09_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=10 THEN cnt ELSE 0 END) AS wartende_ab_10_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=11 THEN cnt ELSE 0 END) AS wartende_ab_11_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=12 THEN cnt ELSE 0 END) AS wartende_ab_12_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=13 THEN cnt ELSE 0 END) AS wartende_ab_13_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=14 THEN cnt ELSE 0 END) AS wartende_ab_14_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=15 THEN cnt ELSE 0 END) AS wartende_ab_15_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=16 THEN cnt ELSE 0 END) AS wartende_ab_16_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=17 THEN cnt ELSE 0 END) AS wartende_ab_17_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=18 THEN cnt ELSE 0 END) AS wartende_ab_18_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=19 THEN cnt ELSE 0 END) AS wartende_ab_19_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=20 THEN cnt ELSE 0 END) AS wartende_ab_20_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=21 THEN cnt ELSE 0 END) AS wartende_ab_21_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=22 THEN cnt ELSE 0 END) AS wartende_ab_22_termin,
  SUM(CASE WHEN type='termin'  AND bucket_hour=23 THEN cnt ELSE 0 END) AS wartende_ab_23_termin,

  /* avg wait: spontan */
  MAX(CASE WHEN type='spontan' AND bucket_hour= 0 THEN avg_wait_minutes END) AS echte_zeit_ab_00_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 1 THEN avg_wait_minutes END) AS echte_zeit_ab_01_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 2 THEN avg_wait_minutes END) AS echte_zeit_ab_02_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 3 THEN avg_wait_minutes END) AS echte_zeit_ab_03_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 4 THEN avg_wait_minutes END) AS echte_zeit_ab_04_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 5 THEN avg_wait_minutes END) AS echte_zeit_ab_05_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 6 THEN avg_wait_minutes END) AS echte_zeit_ab_06_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 7 THEN avg_wait_minutes END) AS echte_zeit_ab_07_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 8 THEN avg_wait_minutes END) AS echte_zeit_ab_08_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour= 9 THEN avg_wait_minutes END) AS echte_zeit_ab_09_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=10 THEN avg_wait_minutes END) AS echte_zeit_ab_10_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=11 THEN avg_wait_minutes END) AS echte_zeit_ab_11_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=12 THEN avg_wait_minutes END) AS echte_zeit_ab_12_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=13 THEN avg_wait_minutes END) AS echte_zeit_ab_13_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=14 THEN avg_wait_minutes END) AS echte_zeit_ab_14_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=15 THEN avg_wait_minutes END) AS echte_zeit_ab_15_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=16 THEN avg_wait_minutes END) AS echte_zeit_ab_16_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=17 THEN avg_wait_minutes END) AS echte_zeit_ab_17_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=18 THEN avg_wait_minutes END) AS echte_zeit_ab_18_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=19 THEN avg_wait_minutes END) AS echte_zeit_ab_19_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=20 THEN avg_wait_minutes END) AS echte_zeit_ab_20_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=21 THEN avg_wait_minutes END) AS echte_zeit_ab_21_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=22 THEN avg_wait_minutes END) AS echte_zeit_ab_22_spontan,
  MAX(CASE WHEN type='spontan' AND bucket_hour=23 THEN avg_wait_minutes END) AS echte_zeit_ab_23_spontan,

  /* avg wait: termin */
  MAX(CASE WHEN type='termin'  AND bucket_hour= 0 THEN avg_wait_minutes END) AS echte_zeit_ab_00_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 1 THEN avg_wait_minutes END) AS echte_zeit_ab_01_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 2 THEN avg_wait_minutes END) AS echte_zeit_ab_02_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 3 THEN avg_wait_minutes END) AS echte_zeit_ab_03_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 4 THEN avg_wait_minutes END) AS echte_zeit_ab_04_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 5 THEN avg_wait_minutes END) AS echte_zeit_ab_05_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 6 THEN avg_wait_minutes END) AS echte_zeit_ab_06_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 7 THEN avg_wait_minutes END) AS echte_zeit_ab_07_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 8 THEN avg_wait_minutes END) AS echte_zeit_ab_08_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour= 9 THEN avg_wait_minutes END) AS echte_zeit_ab_09_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=10 THEN avg_wait_minutes END) AS echte_zeit_ab_10_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=11 THEN avg_wait_minutes END) AS echte_zeit_ab_11_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=12 THEN avg_wait_minutes END) AS echte_zeit_ab_12_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=13 THEN avg_wait_minutes END) AS echte_zeit_ab_13_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=14 THEN avg_wait_minutes END) AS echte_zeit_ab_14_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=15 THEN avg_wait_minutes END) AS echte_zeit_ab_15_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=16 THEN avg_wait_minutes END) AS echte_zeit_ab_16_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=17 THEN avg_wait_minutes END) AS echte_zeit_ab_17_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=18 THEN avg_wait_minutes END) AS echte_zeit_ab_18_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=19 THEN avg_wait_minutes END) AS echte_zeit_ab_19_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=20 THEN avg_wait_minutes END) AS echte_zeit_ab_20_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=21 THEN avg_wait_minutes END) AS echte_zeit_ab_21_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=22 THEN avg_wait_minutes END) AS echte_zeit_ab_22_termin,
  MAX(CASE WHEN type='termin'  AND bucket_hour=23 THEN avg_wait_minutes END) AS echte_zeit_ab_23_termin,

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

-- 4) Insert/update only for scopes without existing rows on the date

INSERT INTO wartenrstatistik (standortid, datum)
SELECT scope_id, datum FROM tmp_pivot;

UPDATE wartenrstatistik w
JOIN tmp_pivot p ON p.scope_id = w.standortid AND p.datum = w.datum
SET
  /* counts: spontan */
  w.wartende_ab_00_spontan = COALESCE(p.wartende_ab_00_spontan, 0),
  w.wartende_ab_01_spontan = COALESCE(p.wartende_ab_01_spontan, 0),
  w.wartende_ab_02_spontan = COALESCE(p.wartende_ab_02_spontan, 0),
  w.wartende_ab_03_spontan = COALESCE(p.wartende_ab_03_spontan, 0),
  w.wartende_ab_04_spontan = COALESCE(p.wartende_ab_04_spontan, 0),
  w.wartende_ab_05_spontan = COALESCE(p.wartende_ab_05_spontan, 0),
  w.wartende_ab_06_spontan = COALESCE(p.wartende_ab_06_spontan, 0),
  w.wartende_ab_07_spontan = COALESCE(p.wartende_ab_07_spontan, 0),
  w.wartende_ab_08_spontan = COALESCE(p.wartende_ab_08_spontan, 0),
  w.wartende_ab_09_spontan = COALESCE(p.wartende_ab_09_spontan, 0),
  w.wartende_ab_10_spontan = COALESCE(p.wartende_ab_10_spontan, 0),
  w.wartende_ab_11_spontan = COALESCE(p.wartende_ab_11_spontan, 0),
  w.wartende_ab_12_spontan = COALESCE(p.wartende_ab_12_spontan, 0),
  w.wartende_ab_13_spontan = COALESCE(p.wartende_ab_13_spontan, 0),
  w.wartende_ab_14_spontan = COALESCE(p.wartende_ab_14_spontan, 0),
  w.wartende_ab_15_spontan = COALESCE(p.wartende_ab_15_spontan, 0),
  w.wartende_ab_16_spontan = COALESCE(p.wartende_ab_16_spontan, 0),
  w.wartende_ab_17_spontan = COALESCE(p.wartende_ab_17_spontan, 0),
  w.wartende_ab_18_spontan = COALESCE(p.wartende_ab_18_spontan, 0),
  w.wartende_ab_19_spontan = COALESCE(p.wartende_ab_19_spontan, 0),
  w.wartende_ab_20_spontan = COALESCE(p.wartende_ab_20_spontan, 0),
  w.wartende_ab_21_spontan = COALESCE(p.wartende_ab_21_spontan, 0),
  w.wartende_ab_22_spontan = COALESCE(p.wartende_ab_22_spontan, 0),
  w.wartende_ab_23_spontan = COALESCE(p.wartende_ab_23_spontan, 0),

  /* counts: termin */
  w.wartende_ab_00_termin = COALESCE(p.wartende_ab_00_termin, 0),
  w.wartende_ab_01_termin = COALESCE(p.wartende_ab_01_termin, 0),
  w.wartende_ab_02_termin = COALESCE(p.wartende_ab_02_termin, 0),
  w.wartende_ab_03_termin = COALESCE(p.wartende_ab_03_termin, 0),
  w.wartende_ab_04_termin = COALESCE(p.wartende_ab_04_termin, 0),
  w.wartende_ab_05_termin = COALESCE(p.wartende_ab_05_termin, 0),
  w.wartende_ab_06_termin = COALESCE(p.wartende_ab_06_termin, 0),
  w.wartende_ab_07_termin = COALESCE(p.wartende_ab_07_termin, 0),
  w.wartende_ab_08_termin = COALESCE(p.wartende_ab_08_termin, 0),
  w.wartende_ab_09_termin = COALESCE(p.wartende_ab_09_termin, 0),
  w.wartende_ab_10_termin = COALESCE(p.wartende_ab_10_termin, 0),
  w.wartende_ab_11_termin = COALESCE(p.wartende_ab_11_termin, 0),
  w.wartende_ab_12_termin = COALESCE(p.wartende_ab_12_termin, 0),
  w.wartende_ab_13_termin = COALESCE(p.wartende_ab_13_termin, 0),
  w.wartende_ab_14_termin = COALESCE(p.wartende_ab_14_termin, 0),
  w.wartende_ab_15_termin = COALESCE(p.wartende_ab_15_termin, 0),
  w.wartende_ab_16_termin = COALESCE(p.wartende_ab_16_termin, 0),
  w.wartende_ab_17_termin = COALESCE(p.wartende_ab_17_termin, 0),
  w.wartende_ab_18_termin = COALESCE(p.wartende_ab_18_termin, 0),
  w.wartende_ab_19_termin = COALESCE(p.wartende_ab_19_termin, 0),
  w.wartende_ab_20_termin = COALESCE(p.wartende_ab_20_termin, 0),
  w.wartende_ab_21_termin = COALESCE(p.wartende_ab_21_termin, 0),
  w.wartende_ab_22_termin = COALESCE(p.wartende_ab_22_termin, 0),
  w.wartende_ab_23_termin = COALESCE(p.wartende_ab_23_termin, 0),

  /* avg wait: spontan */
  w.echte_zeit_ab_00_spontan = COALESCE(p.echte_zeit_ab_00_spontan, 0),
  w.echte_zeit_ab_01_spontan = COALESCE(p.echte_zeit_ab_01_spontan, 0),
  w.echte_zeit_ab_02_spontan = COALESCE(p.echte_zeit_ab_02_spontan, 0),
  w.echte_zeit_ab_03_spontan = COALESCE(p.echte_zeit_ab_03_spontan, 0),
  w.echte_zeit_ab_04_spontan = COALESCE(p.echte_zeit_ab_04_spontan, 0),
  w.echte_zeit_ab_05_spontan = COALESCE(p.echte_zeit_ab_05_spontan, 0),
  w.echte_zeit_ab_06_spontan = COALESCE(p.echte_zeit_ab_06_spontan, 0),
  w.echte_zeit_ab_07_spontan = COALESCE(p.echte_zeit_ab_07_spontan, 0),
  w.echte_zeit_ab_08_spontan = COALESCE(p.echte_zeit_ab_08_spontan, 0),
  w.echte_zeit_ab_09_spontan = COALESCE(p.echte_zeit_ab_09_spontan, 0),
  w.echte_zeit_ab_10_spontan = COALESCE(p.echte_zeit_ab_10_spontan, 0),
  w.echte_zeit_ab_11_spontan = COALESCE(p.echte_zeit_ab_11_spontan, 0),
  w.echte_zeit_ab_12_spontan = COALESCE(p.echte_zeit_ab_12_spontan, 0),
  w.echte_zeit_ab_13_spontan = COALESCE(p.echte_zeit_ab_13_spontan, 0),
  w.echte_zeit_ab_14_spontan = COALESCE(p.echte_zeit_ab_14_spontan, 0),
  w.echte_zeit_ab_15_spontan = COALESCE(p.echte_zeit_ab_15_spontan, 0),
  w.echte_zeit_ab_16_spontan = COALESCE(p.echte_zeit_ab_16_spontan, 0),
  w.echte_zeit_ab_17_spontan = COALESCE(p.echte_zeit_ab_17_spontan, 0),
  w.echte_zeit_ab_18_spontan = COALESCE(p.echte_zeit_ab_18_spontan, 0),
  w.echte_zeit_ab_19_spontan = COALESCE(p.echte_zeit_ab_19_spontan, 0),
  w.echte_zeit_ab_20_spontan = COALESCE(p.echte_zeit_ab_20_spontan, 0),
  w.echte_zeit_ab_21_spontan = COALESCE(p.echte_zeit_ab_21_spontan, 0),
  w.echte_zeit_ab_22_spontan = COALESCE(p.echte_zeit_ab_22_spontan, 0),
  w.echte_zeit_ab_23_spontan = COALESCE(p.echte_zeit_ab_23_spontan, 0),

  /* avg wait: termin */
  w.echte_zeit_ab_00_termin = COALESCE(p.echte_zeit_ab_00_termin, 0),
  w.echte_zeit_ab_01_termin = COALESCE(p.echte_zeit_ab_01_termin, 0),
  w.echte_zeit_ab_02_termin = COALESCE(p.echte_zeit_ab_02_termin, 0),
  w.echte_zeit_ab_03_termin = COALESCE(p.echte_zeit_ab_03_termin, 0),
  w.echte_zeit_ab_04_termin = COALESCE(p.echte_zeit_ab_04_termin, 0),
  w.echte_zeit_ab_05_termin = COALESCE(p.echte_zeit_ab_05_termin, 0),
  w.echte_zeit_ab_06_termin = COALESCE(p.echte_zeit_ab_06_termin, 0),
  w.echte_zeit_ab_07_termin = COALESCE(p.echte_zeit_ab_07_termin, 0),
  w.echte_zeit_ab_08_termin = COALESCE(p.echte_zeit_ab_08_termin, 0),
  w.echte_zeit_ab_09_termin = COALESCE(p.echte_zeit_ab_09_termin, 0),
  w.echte_zeit_ab_10_termin = COALESCE(p.echte_zeit_ab_10_termin, 0),
  w.echte_zeit_ab_11_termin = COALESCE(p.echte_zeit_ab_11_termin, 0),
  w.echte_zeit_ab_12_termin = COALESCE(p.echte_zeit_ab_12_termin, 0),
  w.echte_zeit_ab_13_termin = COALESCE(p.echte_zeit_ab_13_termin, 0),
  w.echte_zeit_ab_14_termin = COALESCE(p.echte_zeit_ab_14_termin, 0),
  w.echte_zeit_ab_15_termin = COALESCE(p.echte_zeit_ab_15_termin, 0),
  w.echte_zeit_ab_16_termin = COALESCE(p.echte_zeit_ab_16_termin, 0),
  w.echte_zeit_ab_17_termin = COALESCE(p.echte_zeit_ab_17_termin, 0),
  w.echte_zeit_ab_18_termin = COALESCE(p.echte_zeit_ab_18_termin, 0),
  w.echte_zeit_ab_19_termin = COALESCE(p.echte_zeit_ab_19_termin, 0),
  w.echte_zeit_ab_20_termin = COALESCE(p.echte_zeit_ab_20_termin, 0),
  w.echte_zeit_ab_21_termin = COALESCE(p.echte_zeit_ab_21_termin, 0),
  w.echte_zeit_ab_22_termin = COALESCE(p.echte_zeit_ab_22_termin, 0),
  w.echte_zeit_ab_23_termin = COALESCE(p.echte_zeit_ab_23_termin, 0),

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

-- Optional cleanup temp tables
DROP TEMPORARY TABLE IF EXISTS tmp_ba_raw;
DROP TEMPORARY TABLE IF EXISTS tmp_ba_agg;
DROP TEMPORARY TABLE IF EXISTS tmp_pivot;