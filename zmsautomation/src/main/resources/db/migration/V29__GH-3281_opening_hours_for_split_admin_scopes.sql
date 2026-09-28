-- Flyway migration: Opening hours for zmsadmin scopes that no longer share a Standort (GH-3281)
--
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin (same calendar day as zms-web).
-- Same window as V19: rest of today until 23:55, next day only when no 5-minute slot fits.
--
-- Terminkunden features moved off a shared Standort. Each scope already offers the
-- service the feature books:
--  - 136 Bürgerbüro Pasing (KVR-II/235) — Führungszeugnis, ZMSKVR-343
--  - 154 Bürgerbüro Leonrodstraße (KVR-II/232) — Führungszeugnis, ZMSKVR-1328
--  - 133 Bürgerbüro Orleansplatz (KVR-II/231) — counter booking, ZMS-1549

SET @slot_seconds := 300;
SET @latest_end := '23:55:00';
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / @slot_seconds) * @slot_seconds);

SET @start_sec := TIME_TO_SEC(@rounded_start);
SET @end_sec := TIME_TO_SEC(@latest_end);
SET @use_next_day := (@start_sec >= 24 * 3600) OR (@end_sec <= @start_sec);

SET @appt_start := IF(@use_next_day, '00:05:00', @rounded_start);
SET @appt_end := @latest_end;

SET @range_start := IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 1 DAY), CURDATE());
SET @range_end :=
  IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY));

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
  (136210, 136, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'GH-3281 Pasing Öffnungszeit',
   0, 5,
   0, 30,
   NOW()),

  (136211, 154, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'GH-3281 Leonrodstraße Öffnungszeit',
   0, 5,
   0, 30,
   NOW()),

  (136212, 133, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'GH-3281 Orleansplatz Öffnungszeit',
   0, 5,
   0, 30,
   NOW());
