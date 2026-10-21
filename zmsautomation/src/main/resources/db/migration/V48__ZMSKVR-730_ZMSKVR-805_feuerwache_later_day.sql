-- ZMSKVR-730 / ZMSKVR-805: jump-in must select the earliest day of the jump-in office.
--
-- V44: Föhring 274 / 10577 has one seat tomorrow.
-- This migration: Feuerwache 6 - Pasing 268 / 10585 has one seat on the day after tomorrow.
-- Jump-in to 10585 must open that later day, not Föhring's earlier day.

UPDATE `standort`
SET `Bearbeitungszeit` = '01:30:00',
    `Termine_ab` = 0,
    `Termine_bis` = 60
WHERE `StandortID` = 268;

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`)
VALUES ('scope', 268, 'appointment', 'startInDaysDefault', '0', NOW())
ON DUPLICATE KEY UPDATE
  `value` = VALUES(`value`),
  `updateTimestamp` = VALUES(`updateTimestamp`);

SET @later_day := DATE_ADD(CURDATE(), INTERVAL 2 DAY);

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
      '(136521,268,''', @later_day, ''',''', @later_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-730 ZMSKVR-805 Feuerwache 6 Pasing later day'',0,1,0,60,NOW(),',
      '268,''', @later_day, ''',''', @later_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-730 ZMSKVR-805 Feuerwache 6 Pasing later day'',0,1,0,60,NOW())'
    )
    WHEN @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136521,268,''', @later_day, ''',''', @later_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-730 ZMSKVR-805 Feuerwache 6 Pasing later day'',0,1,0,60,NOW())'
    )
    ELSE CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
      '(136521,268,''', @later_day, ''',''', @later_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-730 ZMSKVR-805 Feuerwache 6 Pasing later day'',0,1,0,60,NOW())'
    )
  END
);

PREPARE zmskvr730_hours FROM @hours_sql;
EXECUTE zmskvr730_hours;
DEALLOCATE PREPARE zmskvr730_hours;
