-- ZMSKVR-1457 / ZMSKVR-1524: days stay gray when the appointment no longer fits.
--
-- Bürgerbüro Forstenrieder Allee (KVR-II/234 Team 1), Standort 130,
-- provider 10286848. No other Flyway migration gives this scope opening hours.
-- Tomorrow 10:00–10:10 is two 5-minute slots (the end time is exclusive).
-- Führungszeugnis (1063565) and Lebensbescheinigung (10297413) are 1 slot.
-- Auskunft aus dem Gewerbezentralregister, natürliche Person (1064033) is 2 slots.
-- Auskunft aus dem Gewerbezentralregister, juristische Person (10225129) is 3 slots.
-- multiple slots are allowed, so the calendar asks for the real length
-- instead of treating every request as one slot.

SET @day := DATE_ADD(CURDATE(), INTERVAL 1 DAY);

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
      '(136457,130,''', @day, ''',''', @day, ''',1,0,127,''00:00:00'',''10:00:00'',',
      '''00:00:00'',''10:10:00'',''00:05:00'',0,1,',
      '''ZMSKVR-1457 ZMSKVR-1524 Forstenrieder Allee Team 1'',0,5,0,14,NOW(),',
      '130,''', @day, ''',''', @day, ''',1,0,127,''00:00:00'',''10:00:00'',',
      '''00:00:00'',''10:10:00'',''00:05:00'',0,1,',
      '''ZMSKVR-1457 ZMSKVR-1524 Forstenrieder Allee Team 1'',0,5,0,14,NOW())'
    )
    WHEN @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136457,130,''', @day, ''',''', @day, ''',1,0,127,''00:00:00'',''10:00:00'',',
      '''00:00:00'',''10:10:00'',''00:05:00'',0,1,',
      '''ZMSKVR-1457 ZMSKVR-1524 Forstenrieder Allee Team 1'',0,5,0,14,NOW())'
    )
    ELSE CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
      '(136457,130,''', @day, ''',''', @day, ''',1,0,127,''00:00:00'',''10:00:00'',',
      '''00:00:00'',''10:10:00'',''00:05:00'',0,1,',
      '''ZMSKVR-1457 ZMSKVR-1524 Forstenrieder Allee Team 1'',0,5,0,14,NOW())'
    )
  END
);

PREPARE zmskvr1524_hours FROM @hours_sql;
EXECUTE zmskvr1524_hours;
DEALLOCATE PREPARE zmskvr1524_hours;
