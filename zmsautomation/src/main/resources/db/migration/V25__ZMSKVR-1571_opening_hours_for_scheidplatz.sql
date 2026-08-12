-- Flyway migration: Opening hours for Scheidplatz test data (ZMSKVR-1571)
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
