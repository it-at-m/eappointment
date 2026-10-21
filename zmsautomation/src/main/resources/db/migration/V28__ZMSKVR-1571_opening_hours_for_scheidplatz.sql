-- Flyway migration: Opening hours for Scheidplatz test data (ZMSKVR-1571)
--
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin (same calendar day as zms-web).
--
-- Mirrors V19__ZMSKVR-1124_opening_hours_for_ruppertstasse for Citizen View
-- zmskvr-1571_scheidplatz_contact_location_back_login.feature:
--  - Office 102524 (Bürgerbüro Scheidplatz)
--
-- Standorte involved (see V5__standort_test_data):
--  - 157 -> officeId 102524 (alt / Belgradstraße; Termine_bis was 1)
--  - 353 -> officeId 102524 (Riesenfeldstraße; Termine_bis 42)
--
-- Without oeffnungszeit rows, available-calendar returns no free days and ATAF
-- cannot highlight a timeslot for provider 102524.

-- Widen booking window on legacy scope 157 (was Termine_ab=1, Termine_bis=1)
-- and give a real reservation hold (was reservierungsdauer/reservationDuration=0),
-- otherwise Kontakt shows "Ihre Sitzung ist abgelaufen" immediately after reserve.
UPDATE `standort`
SET `Termine_ab` = 0,
    `Termine_bis` = 42,
    `reservierungsdauer` = 15
WHERE `StandortID` = 157;

UPDATE `preferences`
SET `value` = '42',
    `updateTimestamp` = NOW()
WHERE `entity` = 'scope'
  AND `id` = 157
  AND `groupName` = 'appointment'
  AND `name` = 'endInDaysDefault';

UPDATE `preferences`
SET `value` = '15',
    `updateTimestamp` = NOW()
WHERE `entity` = 'scope'
  AND `id` = 157
  AND `groupName` = 'appointment'
  AND `name` = 'reservationDuration';

-- 5-minute Zeitschlitz. Stay on today until 23:55 while at least three hours
-- remain. A shorter stub is not enough for a bookable appointment, so the next
-- day is opened for the whole day, 00:05–23:55.
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
  -- Bürgerbüro Scheidplatz (KVR-II/233) – Belgradstraße alt (officeId 102524)
  (136207, 157, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'ZMSKVR-1571 Scheidplatz Öffnungszeit',
   0, 5,
   0, 30,
   NOW()),

  -- Bürgerbüro Scheidplatz (KVR-II/233) – Riesenfeldstraße (officeId 102524)
  (136208, 353, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'ZMSKVR-1571 Scheidplatz Öffnungszeit',
   0, 5,
   0, 30,
   NOW());
