-- ATAF Flyway path: db/migration-after-hourly → flyway_migration_after_hourly
--
-- GET /scope/347/availability/?startDate=today&endDate=today+21 is empty when the
-- V002 split leaves Schalter C bookable for slot calculation but outside that window.
-- Keep 25-minute coverage on both sides of the V55 scope-switch day (136513) and
-- do not put a seat on the switch day itself.

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

SET @switch_day := NULL;
SET @read_sql := IF(
  @start_col IS NULL,
  'SELECT 1',
  CONCAT(
    'SET @switch_day := (SELECT `', @start_col,
    '` FROM `oeffnungszeit` WHERE `OeffnungszeitID` = 136513 LIMIT 1)'
  )
);
PREPARE ataf_mah_v003_switch FROM @read_sql;
EXECUTE ataf_mah_v003_switch;
DEALLOCATE PREPARE ataf_mah_v003_switch;

SET @cover_start := DATE_FORMAT(CURDATE(), '%Y-%m-%d');
SET @cover_end := DATE_FORMAT(DATE_ADD(CURDATE(), INTERVAL 14 DAY), '%Y-%m-%d');
SET @before_end := IF(
  @switch_day IS NULL,
  @cover_end,
  DATE_FORMAT(DATE_SUB(@switch_day, INTERVAL 1 DAY), '%Y-%m-%d')
);
SET @after_start := IF(
  @switch_day IS NULL,
  NULL,
  DATE_FORMAT(DATE_ADD(@switch_day, INTERVAL 1 DAY), '%Y-%m-%d')
);

SET @seg_count := 1;
SET @count_sql := IF(
  @start_col IS NULL OR @before_end IS NULL OR @cover_start > @before_end,
  'SELECT 1',
  CONCAT(
    'SELECT COUNT(*) INTO @seg_count FROM `oeffnungszeit` WHERE `', @scope_col, '` = 347',
    ' AND `', @slot_col, '` = ''00:25:00''',
    ' AND `', @appt_col, '` <> ''00:00:00''',
    ' AND `', @start_col, '` <= ''', @before_end, '''',
    ' AND `', @end_col, '` >= ''', @cover_start, ''''
  )
);
PREPARE ataf_mah_v003_count_before FROM @count_sql;
EXECUTE ataf_mah_v003_count_before;
DEALLOCATE PREPARE ataf_mah_v003_count_before;

SET @ins_sql := IF(
  @seg_count > 0 OR @start_col IS NULL OR @cover_start > @before_end,
  'SELECT 1',
  IF(
    @has_standort > 0 AND @has_scope > 0,
    CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`,',
      '`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES (',
      '136572,347,''', @cover_start, ''',''', @before_end, ''',1,0,127,''08:00:00'',''08:00:00'',',
      '''18:00:00'',''18:00:00'',''00:25:00'',0,1,',
      '''ZMSKVR-933 Schalter C before scope-switch day'',0,1,0,30,NOW(),',
      '347,''', @cover_start, ''',''', @before_end, ''',1,0,127,''08:00:00'',''08:00:00'',',
      '''18:00:00'',''18:00:00'',''00:25:00'',0,1,',
      '''ZMSKVR-933 Schalter C before scope-switch day'',0,1,0,30,NOW())'
    ),
    IF(
      @has_scope > 0,
      CONCAT(
        'INSERT IGNORE INTO `oeffnungszeit` (',
        '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
        '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
        '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
        '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES (',
        '136572,347,''', @cover_start, ''',''', @before_end, ''',1,0,127,''08:00:00'',''08:00:00'',',
        '''18:00:00'',''18:00:00'',''00:25:00'',0,1,',
        '''ZMSKVR-933 Schalter C before scope-switch day'',0,1,0,30,NOW())'
      ),
      CONCAT(
        'INSERT IGNORE INTO `oeffnungszeit` (',
        '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
        '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
        '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
        '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES (',
        '136572,347,''', @cover_start, ''',''', @before_end, ''',1,0,127,''08:00:00'',''08:00:00'',',
        '''18:00:00'',''18:00:00'',''00:25:00'',0,1,',
        '''ZMSKVR-933 Schalter C before scope-switch day'',0,1,0,30,NOW())'
      )
    )
  )
);
PREPARE ataf_mah_v003_ins_before FROM @ins_sql;
EXECUTE ataf_mah_v003_ins_before;
DEALLOCATE PREPARE ataf_mah_v003_ins_before;

SET @seg_count := 1;
SET @count_sql := IF(
  @start_col IS NULL OR @after_start IS NULL OR @after_start > @cover_end,
  'SELECT 1',
  CONCAT(
    'SELECT COUNT(*) INTO @seg_count FROM `oeffnungszeit` WHERE `', @scope_col, '` = 347',
    ' AND `', @slot_col, '` = ''00:25:00''',
    ' AND `', @appt_col, '` <> ''00:00:00''',
    ' AND `', @start_col, '` <= ''', @cover_end, '''',
    ' AND `', @end_col, '` >= ''', @after_start, ''''
  )
);
PREPARE ataf_mah_v003_count_after FROM @count_sql;
EXECUTE ataf_mah_v003_count_after;
DEALLOCATE PREPARE ataf_mah_v003_count_after;

SET @ins_sql := IF(
  @seg_count > 0 OR @start_col IS NULL OR @after_start IS NULL OR @after_start > @cover_end,
  'SELECT 1',
  IF(
    @has_standort > 0 AND @has_scope > 0,
    CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`,',
      '`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES (',
      '136573,347,''', @after_start, ''',''', @cover_end, ''',1,0,127,''08:00:00'',''08:00:00'',',
      '''18:00:00'',''18:00:00'',''00:25:00'',0,1,',
      '''ZMSKVR-933 Schalter C after scope-switch day'',0,1,0,30,NOW(),',
      '347,''', @after_start, ''',''', @cover_end, ''',1,0,127,''08:00:00'',''08:00:00'',',
      '''18:00:00'',''18:00:00'',''00:25:00'',0,1,',
      '''ZMSKVR-933 Schalter C after scope-switch day'',0,1,0,30,NOW())'
    ),
    IF(
      @has_scope > 0,
      CONCAT(
        'INSERT IGNORE INTO `oeffnungszeit` (',
        '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
        '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
        '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
        '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES (',
        '136573,347,''', @after_start, ''',''', @cover_end, ''',1,0,127,''08:00:00'',''08:00:00'',',
        '''18:00:00'',''18:00:00'',''00:25:00'',0,1,',
        '''ZMSKVR-933 Schalter C after scope-switch day'',0,1,0,30,NOW())'
      ),
      CONCAT(
        'INSERT IGNORE INTO `oeffnungszeit` (',
        '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
        '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
        '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
        '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES (',
        '136573,347,''', @after_start, ''',''', @cover_end, ''',1,0,127,''08:00:00'',''08:00:00'',',
        '''18:00:00'',''18:00:00'',''00:25:00'',0,1,',
        '''ZMSKVR-933 Schalter C after scope-switch day'',0,1,0,30,NOW())'
      )
    )
  )
);
PREPARE ataf_mah_v003_ins_after FROM @ins_sql;
EXECUTE ataf_mah_v003_ins_after;
DEALLOCATE PREPARE ataf_mah_v003_ins_after;
