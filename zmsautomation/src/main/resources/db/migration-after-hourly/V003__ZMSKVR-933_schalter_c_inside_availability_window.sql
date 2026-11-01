-- ATAF Flyway path: db/migration-after-hourly → flyway_migration_after_hourly
--
-- GET /scope/347/availability/?startDate=today&endDate=today+21 stays empty when the
-- stored start_date sits past that window. Slot calculation still logs the row, because
-- it only requires end_date to be in the future. Write the dates with CURDATE() in the
-- SQL text. A concatenated DATE user variable was not a reliable start.
-- Today through +6 days stays before the V55 scope-switch day (today+8 or today+9).

SET @has_de := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'Startdatum'
);
SET @has_en := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'start_date'
);
SET @has_scope := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'scope_id'
);
SET @has_standort := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'StandortID'
);

SET @start_col := IF(@has_en > 0, 'start_date', IF(@has_de > 0, 'Startdatum', NULL));
SET @end_col := IF(@has_en > 0, 'end_date', IF(@has_de > 0, 'Endedatum', NULL));
SET @scope_col := IF(@has_scope > 0, 'scope_id', IF(@has_standort > 0, 'StandortID', NULL));
SET @slot_col := IF(@has_en > 0, 'time_slot', IF(@has_de > 0, 'Timeslot', NULL));
SET @appt_col := IF(@has_en > 0, 'appointment_start_time', IF(@has_de > 0, 'Terminanfangszeit', NULL));

SET @fix_sql := IF(
  @start_col IS NULL,
  'SELECT 1',
  IF(
    @has_standort > 0 AND @has_scope > 0,
    'UPDATE `oeffnungszeit` SET `StandortID` = 347, `scope_id` = 347, `Startdatum` = CURDATE(), `start_date` = CURDATE(), `Endedatum` = DATE_ADD(CURDATE(), INTERVAL 6 DAY), `end_date` = DATE_ADD(CURDATE(), INTERVAL 6 DAY), `Wochentag` = 127, `weekday` = 127, `Anfangszeit` = ''08:00:00'', `start_time` = ''08:00:00'', `Terminanfangszeit` = ''08:00:00'', `appointment_start_time` = ''08:00:00'', `Endzeit` = ''18:00:00'', `end_time` = ''18:00:00'', `Terminendzeit` = ''18:00:00'', `appointment_end_time` = ''18:00:00'', `Timeslot` = ''00:25:00'', `time_slot` = ''00:25:00'', `Anzahlterminarbeitsplaetze` = 1, `appointment_workstation_count` = 1, `Offen_ab` = 0, `open_from_days` = 0, `Offen_bis` = 30, `open_until_days` = 30 WHERE `OeffnungszeitID` = 136570',
    IF(
      @has_scope > 0,
      'UPDATE `oeffnungszeit` SET `scope_id` = 347, `start_date` = CURDATE(), `end_date` = DATE_ADD(CURDATE(), INTERVAL 6 DAY), `weekday` = 127, `start_time` = ''08:00:00'', `appointment_start_time` = ''08:00:00'', `end_time` = ''18:00:00'', `appointment_end_time` = ''18:00:00'', `time_slot` = ''00:25:00'', `appointment_workstation_count` = 1, `open_from_days` = 0, `open_until_days` = 30 WHERE `OeffnungszeitID` = 136570',
      'UPDATE `oeffnungszeit` SET `StandortID` = 347, `Startdatum` = CURDATE(), `Endedatum` = DATE_ADD(CURDATE(), INTERVAL 6 DAY), `Wochentag` = 127, `Anfangszeit` = ''08:00:00'', `Terminanfangszeit` = ''08:00:00'', `Endzeit` = ''18:00:00'', `Terminendzeit` = ''18:00:00'', `Timeslot` = ''00:25:00'', `Anzahlterminarbeitsplaetze` = 1, `Offen_ab` = 0, `Offen_bis` = 30 WHERE `OeffnungszeitID` = 136570'
    )
  )
);
PREPARE ataf_mah_v003_fix FROM @fix_sql;
EXECUTE ataf_mah_v003_fix;
SET @updated := ROW_COUNT();
DEALLOCATE PREPARE ataf_mah_v003_fix;

SET @ins_sql := IF(
  @start_col IS NULL OR @updated > 0,
  'SELECT 1',
  IF(
    @has_standort > 0 AND @has_scope > 0,
    'INSERT INTO `oeffnungszeit` (`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,`Offen_ab`,`Offen_bis`,`updateTimestamp`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES (136570,347,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 6 DAY),1,0,127,''08:00:00'',''08:00:00'',''18:00:00'',''18:00:00'',''00:25:00'',0,1,''ZMSKVR-933 Schalter C 25-minute slots'',0,1,0,30,NOW(),347,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 6 DAY),1,0,127,''08:00:00'',''08:00:00'',''18:00:00'',''18:00:00'',''00:25:00'',0,1,''ZMSKVR-933 Schalter C 25-minute slots'',0,1,0,30,NOW())',
    IF(
      @has_scope > 0,
      'INSERT INTO `oeffnungszeit` (`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES (136570,347,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 6 DAY),1,0,127,''08:00:00'',''08:00:00'',''18:00:00'',''18:00:00'',''00:25:00'',0,1,''ZMSKVR-933 Schalter C 25-minute slots'',0,1,0,30,NOW())',
      'INSERT INTO `oeffnungszeit` (`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES (136570,347,CURDATE(),DATE_ADD(CURDATE(), INTERVAL 6 DAY),1,0,127,''08:00:00'',''08:00:00'',''18:00:00'',''18:00:00'',''00:25:00'',0,1,''ZMSKVR-933 Schalter C 25-minute slots'',0,1,0,30,NOW())'
    )
  )
);
PREPARE ataf_mah_v003_ins FROM @ins_sql;
EXECUTE ataf_mah_v003_ins;
DEALLOCATE PREPARE ataf_mah_v003_ins;

SET @visible := 0;
SET @check_sql := IF(
  @start_col IS NULL,
  'SELECT 1',
  CONCAT(
    'SELECT COUNT(*) INTO @visible FROM `oeffnungszeit` WHERE `', @scope_col, '` = 347',
    ' AND `', @slot_col, '` = ''00:25:00''',
    ' AND `', @appt_col, '` <> ''00:00:00''',
    ' AND `', @start_col, '` <= DATE_ADD(CURDATE(), INTERVAL 21 DAY)',
    ' AND `', @end_col, '` >= CURDATE()'
  )
);
PREPARE ataf_mah_v003_check FROM @check_sql;
EXECUTE ataf_mah_v003_check;
DEALLOCATE PREPARE ataf_mah_v003_check;

SET @fail_sql := IF(
  @start_col IS NULL OR @visible > 0,
  'SELECT 1',
  'SIGNAL SQLSTATE ''45000'' SET MESSAGE_TEXT = ''scope 347 has no 25-minute opening in the next 21 days'''
);
PREPARE ataf_mah_v003_fail FROM @fail_sql;
EXECUTE ataf_mah_v003_fail;
DEALLOCATE PREPARE ataf_mah_v003_fail;
