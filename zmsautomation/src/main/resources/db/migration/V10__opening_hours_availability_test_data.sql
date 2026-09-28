-- Flyway migration: Opening hours availability test data
--
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin (same calendar day as zms-web).
--
-- Ruppertstraße Standorte 160, 181, 172, 184 (offices 10489 / 10502) live in
-- V19__ZMSKVR-1124_zmscitizenapi_opening_hours_for_ruppertstasse.sql — not duplicated here.

-- 5-minute Zeitschlitz. Stay on today from the next slot until 23:55, the latest
-- end at which that slot still fits. The next day (00:05–23:55) is used only when
-- the rounded start is 23:55 or 24:00:00 and no complete slot remains.
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
(136180, 1,   @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136181, 2,   @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136186, 175, @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136187, 127, @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136188, 169, @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136189, 93,  @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136190, 253, @range_start, @range_end, 1, 0, 127, '00:00:00', @appt_start, '00:00:00', @appt_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW());