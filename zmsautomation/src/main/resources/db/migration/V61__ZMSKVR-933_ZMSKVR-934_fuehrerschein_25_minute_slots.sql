-- ZMSKVR-933 / ZMSKVR-934: Führerscheinstelle (KVR-II/44211) Allgemeinschalter A–C
-- use a 25-minute availability slot (not 5/12). Provider.data.slotTimeInMinutes
-- seeds new Öffnungszeiten; oeffnungszeit.Timeslot / time_slot is the live grid.

UPDATE `standort`
SET `Bearbeitungszeit` = '00:25:00'
WHERE `StandortID` IN (6, 256, 347);

UPDATE `provider`
SET `data` = REPLACE(`data`, '"slotTimeInMinutes":20', '"slotTimeInMinutes":25')
WHERE `source` = 'dldb'
  AND `id` = '10308174'
  AND `data` LIKE '%"slotTimeInMinutes":20%';

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

-- Existing Führerscheinstelle openings (V54 range + V55 scope-switch day).
SET @upd_sql := (
  SELECT CASE
    WHEN @has_de > 0 AND @has_en > 0 THEN
      CONCAT(
        'UPDATE `oeffnungszeit` SET `Timeslot` = ''00:25:00'', `time_slot` = ''00:25:00'' ',
        'WHERE `OeffnungszeitID` IN (136380, 136381, 136513, 136514) ',
        'OR `StandortID` IN (6, 256, 347) OR `scope_id` IN (6, 256, 347)'
      )
    WHEN @has_en > 0 THEN
      CONCAT(
        'UPDATE `oeffnungszeit` SET `time_slot` = ''00:25:00'' ',
        'WHERE `OeffnungszeitID` IN (136380, 136381, 136513, 136514) ',
        'OR `scope_id` IN (6, 256, 347)'
      )
    ELSE
      CONCAT(
        'UPDATE `oeffnungszeit` SET `Timeslot` = ''00:25:00'' ',
        'WHERE `OeffnungszeitID` IN (136380, 136381, 136513, 136514) ',
        'OR `StandortID` IN (6, 256, 347)'
      )
  END
);
PREPARE zmskvr933_upd FROM @upd_sql;
EXECUTE zmskvr933_upd;
DEALLOCATE PREPARE zmskvr933_upd;

-- Allgemeinschalter C had no ATAF openings yet; add a short bookable window.
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
PREPARE zmskvr933_ins FROM @hours_sql;
EXECUTE zmskvr933_ins;
DEALLOCATE PREPARE zmskvr933_ins;
