-- ZMSKVR-1235 / ZMSKVR-1244: Feuerwache empty-state callout fixtures.
--
-- Führungen auf den Feuerwachen (10389330):
--   Feuerwache 8 - Föhring (Standort 274, office 10577): one 90-minute internet seat tomorrow.
--   Feuerwache 7 - Milbertshofen (Standort 271, office 10579): no opening hours; custom
--   infoForAllAppointments for the empty-state "Wann gibt es neue Termine?" link.
-- Other Feuerwachen stay without opening hours so Ort checkboxes can isolate empty providers.
--
-- dldb: duration 90, slotTimeInMinutes 90, forceSlotTimeUpdate. Scope Bearbeitungszeit is
-- aligned to 01:30:00. startInDaysDefault / Termine_ab set to 0 so tomorrow is bookable.

UPDATE `standort`
SET `Bearbeitungszeit` = '01:30:00',
    `Termine_ab` = 0,
    `Termine_bis` = 60
WHERE `StandortID` IN (271, 274);

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`)
VALUES
  ('scope', 271, 'appointment', 'startInDaysDefault', '0', NOW()),
  ('scope', 274, 'appointment', 'startInDaysDefault', '0', NOW()),
  (
    'scope',
    271,
    'appointment',
    'infoForAllAppointments',
    '<p>ATAF Hinweis Feuerwache 7: Termine freitags.</p>',
    NOW()
  )
ON DUPLICATE KEY UPDATE
  `value` = VALUES(`value`),
  `updateTimestamp` = VALUES(`updateTimestamp`);

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
      '(136520,274,''', @day, ''',''', @day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-1235 ZMSKVR-1244 Feuerwache 8 Föhring one seat'',0,1,0,60,NOW(),',
      '274,''', @day, ''',''', @day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-1235 ZMSKVR-1244 Feuerwache 8 Föhring one seat'',0,1,0,60,NOW())'
    )
    WHEN @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136520,274,''', @day, ''',''', @day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-1235 ZMSKVR-1244 Feuerwache 8 Föhring one seat'',0,1,0,60,NOW())'
    )
    ELSE CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
      '(136520,274,''', @day, ''',''', @day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,1,',
      '''ZMSKVR-1235 ZMSKVR-1244 Feuerwache 8 Föhring one seat'',0,1,0,60,NOW())'
    )
  END
);

PREPARE zmskvr1235_hours FROM @hours_sql;
EXECUTE zmskvr1235_hours;
DEALLOCATE PREPARE zmskvr1235_hours;
