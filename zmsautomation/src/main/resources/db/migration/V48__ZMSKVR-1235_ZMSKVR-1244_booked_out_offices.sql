-- ZMSKVR-1235 / ZMSKVR-1244: one office per booked-out scenario.
--
-- UI scenarios in one browser job run in parallel and share this database.
-- Pasing 268 / 10585 stays the later-day office (V45) and is not emptied here.
--   Feuerwache 9 - Neuperlach (Standort 277, office 10582): one seat on day+3.
--   Feuerwache 10 - Riem (Standort 280, office 10575): one seat on day+4.
-- Each booked-out scenario reserves only its own office, so cleanup cannot
-- put seats back under the other scenario.

UPDATE `standort`
SET `Bearbeitungszeit` = '01:30:00',
    `Termine_ab` = 0,
    `Termine_bis` = 60
WHERE `StandortID` IN (277, 280);

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`)
VALUES
  ('scope', 277, 'appointment', 'startInDaysDefault', '0', NOW()),
  ('scope', 280, 'appointment', 'startInDaysDefault', '0', NOW())
ON DUPLICATE KEY UPDATE
  `value` = VALUES(`value`),
  `updateTimestamp` = VALUES(`updateTimestamp`);

SET @neuperlach_day := DATE_ADD(CURDATE(), INTERVAL 3 DAY);
SET @riem_day := DATE_ADD(CURDATE(), INTERVAL 4 DAY);

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
      '(136522,277,''', @neuperlach_day, ''',''', @neuperlach_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-1235 ZMSKVR-1244 Feuerwache 9 Neuperlach booked-out'',0,1,0,60,NOW(),',
      '277,''', @neuperlach_day, ''',''', @neuperlach_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-1235 ZMSKVR-1244 Feuerwache 9 Neuperlach booked-out'',0,1,0,60,NOW()),',
      '(136523,280,''', @riem_day, ''',''', @riem_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-1235 ZMSKVR-1244 Feuerwache 10 Riem booked-out'',0,1,0,60,NOW(),',
      '280,''', @riem_day, ''',''', @riem_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-1235 ZMSKVR-1244 Feuerwache 10 Riem booked-out'',0,1,0,60,NOW())'
    )
    WHEN @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136522,277,''', @neuperlach_day, ''',''', @neuperlach_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-1235 ZMSKVR-1244 Feuerwache 9 Neuperlach booked-out'',0,1,0,60,NOW()),',
      '(136523,280,''', @riem_day, ''',''', @riem_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-1235 ZMSKVR-1244 Feuerwache 10 Riem booked-out'',0,1,0,60,NOW())'
    )
    ELSE CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
      '(136522,277,''', @neuperlach_day, ''',''', @neuperlach_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-1235 ZMSKVR-1244 Feuerwache 9 Neuperlach booked-out'',0,1,0,60,NOW()),',
      '(136523,280,''', @riem_day, ''',''', @riem_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-1235 ZMSKVR-1244 Feuerwache 10 Riem booked-out'',0,1,0,60,NOW())'
    )
  END
);

PREPARE zmskvr1244_booked_out_hours FROM @hours_sql;
EXECUTE zmskvr1244_booked_out_hours;
DEALLOCATE PREPARE zmskvr1244_booked_out_hours;
