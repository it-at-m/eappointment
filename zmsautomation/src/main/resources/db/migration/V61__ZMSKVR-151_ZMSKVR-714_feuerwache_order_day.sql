-- ZMSKVR-151 / ZMSKVR-714: dedicated later day for Feuerwache Ort order under Verfügbare Termine.
--
-- V51 gives Riem 10575 a single seat on day+4 for booked-out scenarios that reserve every
-- slot. Parallel citizenview shards empty that seat before the order scenario can open it.
-- Day+7 keeps Pasing / Föhring / Neuperlach / Riem bookable together so list headings can
-- end with Feuerwache 10 without racing the booked-out jobs.

SET @order_day := DATE_ADD(CURDATE(), INTERVAL 7 DAY);

-- Standorte: 268 Pasing 10585, 274 Föhring 10577, 277 Neuperlach 10582, 280 Riem 10575
UPDATE `standort`
SET `Bearbeitungszeit` = '01:30:00',
    `Termine_ab` = 0,
    `Termine_bis` = 60
WHERE `StandortID` IN (268, 274, 277, 280);

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
      '(136560,268,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Pasing'',0,1,0,60,NOW(),',
      '268,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Pasing'',0,1,0,60,NOW()),',
      '(136561,274,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Foehring'',0,1,0,60,NOW(),',
      '274,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Foehring'',0,1,0,60,NOW()),',
      '(136562,277,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Neuperlach'',0,1,0,60,NOW(),',
      '277,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Neuperlach'',0,1,0,60,NOW()),',
      '(136563,280,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Riem'',0,1,0,60,NOW(),',
      '280,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Riem'',0,1,0,60,NOW())'
    )
    WHEN @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136560,268,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Pasing'',0,1,0,60,NOW()),',
      '(136561,274,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Foehring'',0,1,0,60,NOW()),',
      '(136562,277,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Neuperlach'',0,1,0,60,NOW()),',
      '(136563,280,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Riem'',0,1,0,60,NOW())'
    )
    ELSE CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
      '(136560,268,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Pasing'',0,1,0,60,NOW()),',
      '(136561,274,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Foehring'',0,1,0,60,NOW()),',
      '(136562,277,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Neuperlach'',0,1,0,60,NOW()),',
      '(136563,280,''', @order_day, ''',''', @order_day, ''',1,0,127,''00:00:00'',''09:30:00'',',
      '''00:00:00'',''11:00:00'',''01:30:00'',0,3,',
      '''ZMSKVR-151 ZMSKVR-714 Feuerwache order day Riem'',0,1,0,60,NOW())'
    )
  END
);

PREPARE zmskvr151_order_day FROM @hours_sql;
EXECUTE zmskvr151_order_day;
DEALLOCATE PREPARE zmskvr151_order_day;
