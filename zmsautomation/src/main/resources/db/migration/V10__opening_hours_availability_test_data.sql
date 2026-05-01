-- Flyway migration: Opening hours availability test data
--
-- Ruppertstraße Standorte 160, 181, 172, 184 (offices 10489 / 10502) live in
-- V19__ZMSKVR-1124_zmscitizenapi_opening_hours_for_ruppertstasse.sql — not duplicated here.

-- round current time to next 5 minute slot
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / 300) * 300);

-- desired end = +6 hours
SET @desired_end :=
  ADDTIME(@rounded_start, '06:00:00');

-- latest allowed end so slots still fit
SET @rounded_end :=
  LEAST(@desired_end, '23:55:00');

-- If the capped end is not after the rounded start (e.g. late night / 24:00:00 start),
-- use the next calendar day with appointment window 00:05–03:05 (still capped at 23:55).
SET @start_sec := TIME_TO_SEC(@rounded_start);
SET @end_sec := TIME_TO_SEC(@rounded_end);
SET @use_next_day := (@end_sec <= @start_sec);

SET @appt_start := IF(@use_next_day, '00:05:00', @rounded_start);
SET @appt_end :=
  IF(@use_next_day, LEAST(ADDTIME('00:05:00', '03:00:00'), '23:55:00'), @rounded_end);

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
(136180, 1,   @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136181, 2,   @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136186, 175, @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136187, 127, @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136188, 169, @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136189, 93,  @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136190, 253, @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW());