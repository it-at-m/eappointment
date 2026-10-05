-- ZMSKVR-1536 / ZMSKVR-1552: bookable Rente phone and video appointments.
--
-- The catalog already has these variants (not the prod scope ids 394 and 391):
--   service 2 / location 2 / scope 369 — Auskunft zur Rente Telefon
--   service 1 / location 1 / scope 371 — Auskunft zur Rente Video
-- Telephone was off, and neither scope had opening hours, so Meine Termine
-- could not show a booked phone number or a slot.

UPDATE `standort`
SET `telefonaktiviert` = 1,
    `telefonPflichtfeld` = 1,
    `emailPflichtfeld` = 1,
    `Termine_ab` = 0,
    `Termine_bis` = 60,
    `reservierungsdauer` = 15
WHERE `StandortID` IN (369, 371);

-- Same window as V29. CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin.
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

SET @has_de := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'StandortID'
);
SET @has_en := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'scope_id'
);

SET @hours_sql := (
  SELECT CASE
    WHEN @has_de > 0 AND @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`,',
      '`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136369,369,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1536 ZMSKVR-1552 Rente Telefon'',0,5,0,60,NOW(),',
      '369,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1536 ZMSKVR-1552 Rente Telefon'',0,5,0,60,NOW()),',
      '(136371,371,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1536 ZMSKVR-1552 Rente Video'',0,5,0,60,NOW(),',
      '371,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1536 ZMSKVR-1552 Rente Video'',0,5,0,60,NOW())'
    )
    WHEN @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136369,369,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1536 ZMSKVR-1552 Rente Telefon'',0,5,0,60,NOW()),',
      '(136371,371,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1536 ZMSKVR-1552 Rente Video'',0,5,0,60,NOW())'
    )
    ELSE CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
      '(136369,369,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1536 ZMSKVR-1552 Rente Telefon'',0,5,0,60,NOW()),',
      '(136371,371,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1536 ZMSKVR-1552 Rente Video'',0,5,0,60,NOW())'
    )
  END
);

PREPARE zmskvr1552_hours FROM @hours_sql;
EXECUTE zmskvr1552_hours;
DEALLOCATE PREPARE zmskvr1552_hours;
