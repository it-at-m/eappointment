-- ATAF Flyway path: db/migration-after-hourly → history table flyway_migration_after_hourly
-- (zmsautomation-test step 7b: after cronjob.hourly / bundled updateDldbData, before minutly).
-- Hourly reimports source=dldb providers and force-updates availability.slotTimeInMinutes
-- from provider.data — pins in db/migration (flyway_migration) do not survive hourly.
-- Do not put these in munich_sadb_overwrite.json or locations_de.json.
--
-- ZMSKVR-933 / ZMSKVR-934: Führerscheinstelle 10308174 Allgemeinschalter A–C → 25 minutes.

UPDATE `standort`
SET `Bearbeitungszeit` = '00:25:00'
WHERE `StandortID` IN (6, 256, 347);

-- Live SADB / fixtures usually still have 20 (with or without space after ':').
UPDATE `provider`
SET `data` = REGEXP_REPLACE(
  `data`,
  '"slotTimeInMinutes"[[:space:]]*:[[:space:]]*[0-9]+',
  '"slotTimeInMinutes":25'
)
WHERE `source` = 'dldb'
  AND `id` = '10308174'
  AND `data` REGEXP '"slotTimeInMinutes"[[:space:]]*:[[:space:]]*[0-9]+';

SET @has_de := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'Timeslot'
);
SET @has_en := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'time_slot'
);

SET @upd_sql := (
  SELECT CASE
    WHEN @has_de > 0 AND @has_en > 0 THEN
      CONCAT(
        'UPDATE `oeffnungszeit` SET `Timeslot` = ''00:25:00'', `time_slot` = ''00:25:00'' ',
        'WHERE `OeffnungszeitID` IN (136380, 136381, 136513, 136514, 136550) ',
        'OR `StandortID` IN (6, 256, 347) OR `scope_id` IN (6, 256, 347)'
      )
    WHEN @has_en > 0 THEN
      CONCAT(
        'UPDATE `oeffnungszeit` SET `time_slot` = ''00:25:00'' ',
        'WHERE `OeffnungszeitID` IN (136380, 136381, 136513, 136514, 136550) ',
        'OR `scope_id` IN (6, 256, 347)'
      )
    ELSE
      CONCAT(
        'UPDATE `oeffnungszeit` SET `Timeslot` = ''00:25:00'' ',
        'WHERE `OeffnungszeitID` IN (136380, 136381, 136513, 136514, 136550) ',
        'OR `StandortID` IN (6, 256, 347)'
      )
  END
);
PREPARE ataf_mah_upd FROM @upd_sql;
EXECUTE ataf_mah_upd;
DEALLOCATE PREPARE ataf_mah_upd;

SET @range_start := CURDATE();
SET @range_end := DATE_ADD(CURDATE(), INTERVAL 14 DAY);

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
      '(136550,347,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''08:00:00'',',
      '''00:00:00'',''18:00:00'',''00:25:00'',0,1,',
      '''ZMSKVR-933 ZMSKVR-934 Schalter C 25-minute slots'',0,1,0,30,NOW(),',
      '347,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''08:00:00'',',
      '''00:00:00'',''18:00:00'',''00:25:00'',0,1,',
      '''ZMSKVR-933 ZMSKVR-934 Schalter C 25-minute slots'',0,1,0,30,NOW())'
    )
    WHEN @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136550,347,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''08:00:00'',',
      '''00:00:00'',''18:00:00'',''00:25:00'',0,1,',
      '''ZMSKVR-933 ZMSKVR-934 Schalter C 25-minute slots'',0,1,0,30,NOW())'
    )
    ELSE CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
      '(136550,347,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''08:00:00'',',
      '''00:00:00'',''18:00:00'',''00:25:00'',0,1,',
      '''ZMSKVR-933 ZMSKVR-934 Schalter C 25-minute slots'',0,1,0,30,NOW())'
    )
  END
);
PREPARE ataf_mah_ins FROM @hours_sql;
EXECUTE ataf_mah_ins;
DEALLOCATE PREPARE ataf_mah_ins;
