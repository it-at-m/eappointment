-- ZMSKVR-1702: a later Föhring day for the mail-limit rebook.
--
-- Tomorrow (V47/V49, 8 seats) and day+7 (V61, 3 seats) share one 09:30 timestamp.
-- Parallel Führungen bookings fill both before the rebook reserve runs, so
-- appointmentNotAvailable wins and the mail-limit callout never appears.
-- Day+14 is only scope 274 / office 10577, with 8 internet seats, so the
-- citizenview reserve walk can leave a taken 09:30 and still find a free seat.

SET @mail_limit_day := DATE_ADD(CURDATE(), INTERVAL 14 DAY);

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
      '(136571,274,''', @mail_limit_day, ''',''', @mail_limit_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,8,',
      '''ZMSKVR-1702 Feuerwache 8 mail limit day'',0,1,0,60,NOW(),',
      '274,''', @mail_limit_day, ''',''', @mail_limit_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,8,',
      '''ZMSKVR-1702 Feuerwache 8 mail limit day'',0,1,0,60,NOW())'
    )
    WHEN @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136571,274,''', @mail_limit_day, ''',''', @mail_limit_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,8,',
      '''ZMSKVR-1702 Feuerwache 8 mail limit day'',0,1,0,60,NOW())'
    )
    ELSE CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
      '(136571,274,''', @mail_limit_day, ''',''', @mail_limit_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,8,',
      '''ZMSKVR-1702 Feuerwache 8 mail limit day'',0,1,0,60,NOW())'
    )
  END
);

PREPARE zmskvr1702_hours FROM @hours_sql;
EXECUTE zmskvr1702_hours;
DEALLOCATE PREPARE zmskvr1702_hours;
