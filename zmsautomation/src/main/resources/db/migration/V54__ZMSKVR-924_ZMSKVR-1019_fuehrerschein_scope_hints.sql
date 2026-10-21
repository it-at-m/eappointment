-- ZMSKVR-924 / ZMSKVR-1019
-- Distinct Kundenhinweise (standort.Hinweis = scope.hint) and infoForAppointment for
-- Führerscheinstelle Allgemeinschalter A (6) and B (256) under office 10308174.
-- No openings existed for these scopes; add matching windows with one internet seat
-- each so a second same-time reserve switches Schalter (same shape as V50 WB03/WB04).
-- Schalter C (347) keeps differentiated markers but no openings here.

UPDATE `standort`
SET `Hinweis` = 'ATAF Kundenhinweis Schalter A',
    `info_for_appointment` = 'ATAF Termin Hinweis Schalter A',
    `updateTimestamp` = NOW()
WHERE `StandortID` = 6;

UPDATE `standort`
SET `Hinweis` = 'ATAF Kundenhinweis Schalter B',
    `info_for_appointment` = 'ATAF Termin Hinweis Schalter B',
    `updateTimestamp` = NOW()
WHERE `StandortID` = 256;

UPDATE `standort`
SET `Hinweis` = 'ATAF Kundenhinweis Schalter C',
    `info_for_appointment` = 'ATAF Termin Hinweis Schalter C',
    `updateTimestamp` = NOW()
WHERE `StandortID` = 347;

UPDATE `preferences`
SET `value` = 'ATAF Termin Hinweis Schalter A',
    `updateTimestamp` = NOW()
WHERE `entity` = 'scope'
  AND `id` = 6
  AND `groupName` = 'appointment'
  AND `name` = 'infoForAppointment';

UPDATE `preferences`
SET `value` = 'ATAF Termin Hinweis Schalter B',
    `updateTimestamp` = NOW()
WHERE `entity` = 'scope'
  AND `id` = 256
  AND `groupName` = 'appointment'
  AND `name` = 'infoForAppointment';

UPDATE `preferences`
SET `value` = 'ATAF Termin Hinweis Schalter C',
    `updateTimestamp` = NOW()
WHERE `entity` = 'scope'
  AND `id` = 347
  AND `groupName` = 'appointment'
  AND `name` = 'infoForAppointment';

-- Dynamic Berlin window (same pattern as V19).
SET @slot_seconds := 720;
SET @latest_end := '23:48:00';
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / @slot_seconds) * @slot_seconds);

SET @start_sec := TIME_TO_SEC(@rounded_start);
SET @end_sec := TIME_TO_SEC(@latest_end);
SET @use_next_day := (@start_sec >= 24 * 3600) OR (@end_sec <= @start_sec) OR ((@end_sec - @start_sec) < 3 * 3600);

SET @appt_start := IF(@use_next_day, '08:00:00', @rounded_start);
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
  -- Führerscheinstelle Allgemeinschalter A (officeId 10308174)
  (136380, 6, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:12:00',
   0, 1,
   'ZMSKVR-924 ZMSKVR-1019 single internet seat Schalter A',
   0, 1,
   0, 30,
   NOW()),

  -- Führerscheinstelle Allgemeinschalter B (officeId 10308174)
  (136381, 256, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:12:00',
   0, 1,
   'ZMSKVR-924 ZMSKVR-1019 single internet seat Schalter B',
   0, 1,
   0, 30,
   NOW());
