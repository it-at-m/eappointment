-- Flyway migration: Opening hours for Ruppertstraße test data (ZMSKVR-1124)
--
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin (same calendar day as zms-web).
--
-- Mirrors V10__opening_hours_availability_test_data but focuses on
-- Ruppertstraße offices used in Citizen API zmskvr-1124_booking_ruppertstrasse_pass_calendar_jumpin_links_citizenapi.feature:
--  - Office 10489 (Bürgerbüro Ruppertstraße (KVR-II/22))
--  - Office 10492 (Bürgerbüro Ruppertstraße (KVR-II/211))
--  - Office 10502 (Bürgerbüro Ruppertstraße (KVR-II/221))
--
-- Standorte involved (see V5__standort_test_data):
--  - 148 -> officeId 10492 (Abholung, KVR-II/211)
--  - 160 -> officeId 10489 (WB04, KVR-II/22)
--  - 181 -> officeId 10489 (WB03, KVR-II/22)
--  - 172 -> officeId 10502 (WB04 Pass, KVR-II/221)
--  - 184 -> officeId 10502 (WB03 Pass, KVR-II/221)
--  - 342 -> officeId 10502 (Serviceschalter Pass, KVR-II/221)

-- 5-minute Zeitschlitz. Stay on today until 23:55 while at least three hours
-- remain, so 10489 and 10313237 still share a multi-slot window. A shorter stub
-- leaves one timestamp on one office, so the next day is opened 00:05–23:55.
SET @slot_seconds := 300;
SET @latest_end := '23:55:00';
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / @slot_seconds) * @slot_seconds);

SET @start_sec := TIME_TO_SEC(@rounded_start);
SET @end_sec := TIME_TO_SEC(@latest_end);
SET @use_next_day := (@start_sec >= 24 * 3600) OR (@end_sec <= @start_sec) OR ((@end_sec - @start_sec) < 3 * 3600);

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
  -- Bürgerbüro Ruppertstraße (KVR-II/211) – Abholung (officeId 10492)
  (136200, 148, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'ZMSKVR-1124 Ruppertstraße Öffnungszeit',
   0, 5,
   0, 30,
   NOW()),

  -- Bürgerbüro Ruppertstraße (KVR-II/22) – WB04 (officeId 10489)
  (136201, 160, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'ZMSKVR-1124 Ruppertstraße Öffnungszeit',
   0, 5,
   0, 30,
   NOW()),

  -- Bürgerbüro Ruppertstraße (KVR-II/22) – WB03 (officeId 10489)
  (136202, 181, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'ZMSKVR-1124 Ruppertstraße Öffnungszeit',
   0, 5,
   0, 30,
   NOW()),

  -- Bürgerbüro Ruppertstraße (KVR-II/221) – WB04 Pass (officeId 10502)
  (136203, 172, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'ZMSKVR-1124 Ruppertstraße Öffnungszeit',
   0, 5,
   0, 30,
   NOW()),

  -- Bürgerbüro Ruppertstraße (KVR-II/221) – WB03 Pass (officeId 10502)
  (136204, 184, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'ZMSKVR-1124 Ruppertstraße Öffnungszeit',
   0, 5,
   0, 30,
   NOW()),

  -- Bürgerbüro Ruppertstraße (KVR-II/221) – Serviceschalter Pass (officeId 10502)
  (136205, 342, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'ZMSKVR-1124 Ruppertstraße Öffnungszeit',
   0, 5,
   0, 30,
   NOW());

