-- Flyway: isolate Wartebereich / Schalter switch capacity (ZMSKVR-1051 / ZMSKVR-1309)
--
-- V50 put one internet seat on the shared V19 Ruppertstraße window (136201/136202).
-- Parallel Wohnsitzanmeldung scenarios then leave every timestamp with exactly one
-- free seat — the same-timestamp "other Wartebereich" reserve always 404s and the
-- ATAF retry walks the calendar into rateLimitExceeded.
--
-- Same shape for V51 Führerscheinstelle Schalter A/B (136380/136381).
--
-- Restore shared-range capacity, then add a single day after the V19 range (same
-- calendar day as V43 Pass single-seat) with one internet seat per switch scope.
-- The citizenapi switch scenarios book that day only.

-- Same window math as V19 / V43.
SET @slot_seconds := 300;
SET @latest_end := '23:55:00';
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / @slot_seconds) * @slot_seconds);

SET @start_sec := TIME_TO_SEC(@rounded_start);
SET @end_sec := TIME_TO_SEC(@latest_end);
SET @use_next_day := (@start_sec >= 24 * 3600) OR (@end_sec <= @start_sec) OR ((@end_sec - @start_sec) < 3 * 3600);

SET @v19_range_end :=
  IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY));
SET @switch_day := DATE_ADD(@v19_range_end, INTERVAL 1 DAY);

-- Undo V50 squeeze on the shared Hauptkalender window.
UPDATE `oeffnungszeit`
SET `Anzahlterminarbeitsplaetze` = 5,
    `kommentar` = 'ZMSKVR-1124 Ruppertstraße Öffnungszeit'
WHERE `OeffnungszeitID` IN (136201, 136202);

-- Undo V51 one-seat squeeze on the shared Führerscheinstelle window.
UPDATE `oeffnungszeit`
SET `Anzahlterminarbeitsplaetze` = 5,
    `kommentar` = 'ZMSKVR-924 ZMSKVR-1019 Führerscheinstelle Öffnungszeit'
WHERE `OeffnungszeitID` IN (136380, 136381);

INSERT IGNORE INTO `oeffnungszeit`
(
  `OeffnungszeitID`,
  `StandortID`,
  `Startdatum`,
  `Endedatum`,
  `allexWochen`,
  `jedexteWoche`,
  `Wochentag`,
  `Anfangszeit`,
  `Terminanfangszeit`,
  `Endzeit`,
  `Terminendzeit`,
  `Timeslot`,
  `Anzahlarbeitsplaetze`,
  `Anzahlterminarbeitsplaetze`,
  `kommentar`,
  `reduktionTermineImInternet`,
  `erlaubemehrfachslots`,
  `Offen_ab`,
  `Offen_bis`,
  `updateTimestamp`
)
VALUES
  -- Bürgerbüro Ruppertstraße (KVR-II/22) – WB04, one internet seat for switch day
  (136511, 160, @switch_day, @switch_day,
   1, 0, 127,
   '00:00:00', '08:00:00',
   '00:00:00', '18:00:00',
   '00:05:00',
   0, 1,
   'ZMSKVR-1051 ZMSKVR-1309 WB04 scope-switch day after V19 range',
   0, 5,
   0, 30,
   NOW()),

  -- Bürgerbüro Ruppertstraße (KVR-II/22) – WB03, one internet seat for switch day
  (136512, 181, @switch_day, @switch_day,
   1, 0, 127,
   '00:00:00', '08:00:00',
   '00:00:00', '18:00:00',
   '00:05:00',
   0, 1,
   'ZMSKVR-1051 ZMSKVR-1309 WB03 scope-switch day after V19 range',
   0, 5,
   0, 30,
   NOW()),

  -- Führerscheinstelle Allgemeinschalter A, one internet seat for switch day
  (136513, 6, @switch_day, @switch_day,
   1, 0, 127,
   '00:00:00', '08:00:00',
   '00:00:00', '18:00:00',
   '00:12:00',
   0, 1,
   'ZMSKVR-924 ZMSKVR-1019 Schalter A scope-switch day after V19 range',
   0, 1,
   0, 30,
   NOW()),

  -- Führerscheinstelle Allgemeinschalter B, one internet seat for switch day
  (136514, 256, @switch_day, @switch_day,
   1, 0, 127,
   '00:00:00', '08:00:00',
   '00:00:00', '18:00:00',
   '00:12:00',
   0, 1,
   'ZMSKVR-924 ZMSKVR-1019 Schalter B scope-switch day after V19 range',
   0, 1,
   0, 30,
   NOW());
