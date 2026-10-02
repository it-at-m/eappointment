-- ZMSKVR-1501 / ZMSKVR-1509: book Einsicht in Bauakten (45 minutes) at
-- Registratur (PLAN-HAIV-13-ZR), scope 319, provider 10176491.
--
-- The scope existed without opening hours, and appointments started seven days
-- out. A 45-minute visit needs a long enough window. Stay on today while four
-- hours remain; otherwise the next day is open 00:00–23:55.
-- The slot length is 45 minutes. Termin bearbeiten reads that length from the
-- booked availability, so a 5-minute slot shows "(5 min)" on the edit form.
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin.

UPDATE `standort`
SET `Termine_ab` = 0,
    `Termine_bis` = 60
WHERE `StandortID` = 319;

UPDATE `preferences`
SET `value` = '0'
WHERE `entity` = 'scope'
  AND `id` = 319
  AND `groupName` = 'appointment'
  AND `name` = 'startInDaysDefault';

SET @slot_seconds := 2700;
SET @latest_end := '23:55:00';
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / @slot_seconds) * @slot_seconds);

SET @start_sec := TIME_TO_SEC(@rounded_start);
SET @end_sec := TIME_TO_SEC(@latest_end);
SET @use_next_day := (@start_sec >= 24 * 3600) OR (@end_sec <= @start_sec) OR ((@end_sec - @start_sec) < 4 * 3600);

SET @appt_start := IF(@use_next_day, '00:00:00', @rounded_start);
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
      '(136470,319,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:45:00'',0,5,',
      '''ZMSKVR-1501 ZMSKVR-1509 Registratur 45 Minuten'',0,60,0,60,NOW(),',
      '319,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:45:00'',0,5,',
      '''ZMSKVR-1501 ZMSKVR-1509 Registratur 45 Minuten'',0,60,0,60,NOW())'
    )
    WHEN @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136470,319,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:45:00'',0,5,',
      '''ZMSKVR-1501 ZMSKVR-1509 Registratur 45 Minuten'',0,60,0,60,NOW())'
    )
    ELSE CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
      '(136470,319,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:45:00'',0,5,',
      '''ZMSKVR-1501 ZMSKVR-1509 Registratur 45 Minuten'',0,60,0,60,NOW())'
    )
  END
);

PREPARE zmskvr1509_hours FROM @hours_sql;
EXECUTE zmskvr1509_hours;
DEALLOCATE PREPARE zmskvr1509_hours;
