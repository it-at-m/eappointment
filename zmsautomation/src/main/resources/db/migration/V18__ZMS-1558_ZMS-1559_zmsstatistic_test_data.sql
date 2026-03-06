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
  (2, @stats_date, 1, 0, '08:00:00', 5, 1, 10, 'Termin-Kunde 1', 'Güterkraftverkehr – Erlaubnis und Lizenz', 2),
  -- Appeared Spontan-Kunde (1) with service "Taxi oder Mietwagen – Unterlagen nachreichen"
  (2, @stats_date, 0, 0, '08:05:00', 4, 1, 8, 'Spontan-Kunde 1', 'Taxi oder Mietwagen – Unterlagen nachreichen', 1),
  -- Nicht erschienener Termin-Kunde (1) with service "Zulassung Taxi oder Mietwagen"
  (2, @stats_date, 1, 1, '08:10:00', 0, 1, 0, 'No-Show Termin 1', 'Zulassung Taxi oder Mietwagen', 0),
  -- Nicht erschienener Spontan-Kunde (1) with service "Zulassung Taxi oder Mietwagen"
  (2, @stats_date, 0, 1, '08:15:00', 0, 1, 0, 'No-Show Spontan 1', 'Zulassung Taxi oder Mietwagen', 0);


