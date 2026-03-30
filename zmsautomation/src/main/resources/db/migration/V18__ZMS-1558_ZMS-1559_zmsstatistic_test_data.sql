-- V18__ZMS-1558_ZMS-1559

-- Flyway migration: zmsstatistic test data for scope 2 (Gewerbeamt (KVR-III/23) Verkehr)
--
-- Goal:
--  - Provide deterministic statistics test data for the zmsstatistic Cucumber scenarios
--    that use Standort "Gewerbeamt (KVR-III/23) Verkehr" (scope/StandortID = 2).
--  - Use dynamic dates similar to V10__opening_hours_availability_test_data.sql so
--    tests remain stable over time while still referring to "yesterday".
--
-- Important:
--  - We only touch rows for standortid = 2 and the synthetic test date (yesterday).
--  - Existing data for other standorte or dates is left untouched.

-- Use previous calendar day as statistics date
SET @stats_date := DATE_SUB(CURDATE(), INTERVAL 1 DAY);

-- Clean up potential leftovers for deterministic test runs
DELETE FROM `buergerarchiv`
WHERE `StandortID` = 2
  AND `Datum` = @stats_date;

DELETE FROM `statistik`
WHERE `standortid` = 2
  AND `datum` = @stats_date;

-- Seed Kundenstatistik / Dienstleistungsstatistik data for scope 2:
--  - Erschienene Kunden: 2 (1 Termin, 1 Spontan)
--  - Nicht erschienene Kunden: 2 (1 Termin, 1 Spontan)
--  - Erschienene Termin-Kunden: 1
--  - Nicht erschienene Termin-Kunden: 1
--  - Erschienene Spontan-Kunden: 1
--  - Nicht erschienene Spontan-Kunden: 1
--  - Dienstleistungen (Tag): 3 distinct services
--  - Dienstleistungsstatistik rows (previous day):
--      * "Güterkraftverkehr – Erlaubnis und Lizenz"            => 1
--      * "Taxi oder Mietwagen – Unterlagen nachreichen"        => 1
--      * "Zulassung Taxi oder Mietwagen"                       => 1
INSERT INTO `buergerarchiv`
(
  `StandortID`,
  `Datum`,
  `mitTermin`,
  `nicht_erschienen`,
  `Timestamp`,
  `wartezeit`,
  `AnzahlPersonen`,
  `bearbeitungszeit`,
  `name`,
  `dienstleistungen`,
  `wegezeit`
)
VALUES
  -- Appeared Termin-Kunde (1) with service "Güterkraftverkehr – Erlaubnis und Lizenz"
  (2, @stats_date, 1, 0, '08:00:00', 5, 1, 10, 'Termin-Kunde 1', 'Güterkraftverkehr – Erlaubnis und Lizenz', 2);
SET @ba1 := LAST_INSERT_ID();

INSERT INTO `buergerarchiv`
(
  `StandortID`,
  `Datum`,
  `mitTermin`,
  `nicht_erschienen`,
  `Timestamp`,
  `wartezeit`,
  `AnzahlPersonen`,
  `bearbeitungszeit`,
  `name`,
  `dienstleistungen`,
  `wegezeit`
)
VALUES
  -- Appeared Spontan-Kunde (1) with service "Taxi oder Mietwagen – Unterlagen nachreichen"
  (2, @stats_date, 0, 0, '08:05:00', 4, 1, 8, 'Spontan-Kunde 1', 'Taxi oder Mietwagen – Unterlagen nachreichen', 1);
SET @ba2 := LAST_INSERT_ID();

INSERT INTO `buergerarchiv`
(
  `StandortID`,
  `Datum`,
  `mitTermin`,
  `nicht_erschienen`,
  `Timestamp`,
  `wartezeit`,
  `AnzahlPersonen`,
  `bearbeitungszeit`,
  `name`,
  `dienstleistungen`,
  `wegezeit`
)
VALUES
  -- Nicht erschienener Termin-Kunde (1) with service "Zulassung Taxi oder Mietwagen"
  (2, @stats_date, 1, 1, '08:10:00', 0, 1, 0, 'No-Show Termin 1', 'Zulassung Taxi oder Mietwagen', 0);
SET @ba3 := LAST_INSERT_ID();

INSERT INTO `buergerarchiv`
(
  `StandortID`,
  `Datum`,
  `mitTermin`,
  `nicht_erschienen`,
  `Timestamp`,
  `wartezeit`,
  `AnzahlPersonen`,
  `bearbeitungszeit`,
  `name`,
  `dienstleistungen`,
  `wegezeit`
)
VALUES
  -- Nicht erschienener Spontan-Kunde (1) with service "Zulassung Taxi oder Mietwagen"
  (2, @stats_date, 0, 1, '08:15:00', 0, 1, 0, 'No-Show Spontan 1', 'Zulassung Taxi oder Mietwagen', 0);
SET @ba4 := LAST_INSERT_ID();

-- Minimal statistik rows associated with the archive entries above.
-- Use real service IDs for anliegenid/info_dl_id to match test data:
--   1063712   -> "Güterkraftverkehr – Erlaubnis und Lizenz"
--   10300793  -> "Taxi oder Mietwagen – Unterlagen nachreichen"
--   10300814  -> "Zulassung Taxi oder Mietwagen"
INSERT INTO `statistik`
(
  `kundenid`,
  `organisationsid`,
  `behoerdenid`,
  `clusterid`,
  `standortid`,
  `anliegenid`,
  `datum`,
  `lastbuergerarchivid`,
  `termin`,
  `info_dl_id`,
  `bearbeitungszeit`
)
VALUES
  (1, 1, 1, 1, 2, 1063712,  @stats_date, @ba1, 1, 1063712, 10),
  (1, 1, 1, 1, 2, 10300793, @stats_date, @ba2, 0, 10300793, 8),
  (1, 1, 1, 1, 2, 10300814, @stats_date, @ba3, 1, 10300814, 0),
  (1, 1, 1, 1, 2, 10300814, @stats_date, @ba4, 0, 10300814, 0);

