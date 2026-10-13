-- ATAF Flyway path: db/migration-after-hourly → flyway_migration_after_hourly
--
-- V001 opens Schalter C (347 / OeffnungszeitID 136550) for CURDATE()…+14 days with one
-- internet seat. That band overlaps the V55 scope-switch day (136513 / 136514) which must
-- stay A/B-only so "second same-timestamp reserve switches Schalter" stays A↔B.
-- Split C around that day; keep 25-minute coverage on every other day for ZMSKVR-933.
--
-- English-only CI has start_date/end_date, not Startdatum.
-- Date reads MUST use PREPARE of a dynamic SQL string: MariaDB still resolves
-- column names in BOTH branches of IF(cond, (SELECT col…), …), so embedding
-- SELECTs in IF() fails with 1054 Unknown column on EN-only schemas.

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

SET @start_col := IF(@has_en > 0, 'start_date', IF(@has_de > 0, 'Startdatum', NULL));
SET @end_col := IF(@has_en > 0, 'end_date', IF(@has_de > 0, 'Endedatum', NULL));

SET @switch_day := NULL;
SET @c_start := NULL;
SET @c_end := NULL;

SET @read_sql := IF(
  @start_col IS NULL,
  'SELECT 1',
  CONCAT(
    'SET @switch_day := (SELECT `', @start_col,
    '` FROM `oeffnungszeit` WHERE `OeffnungszeitID` = 136513 LIMIT 1)'
  )
);
PREPARE ataf_mah_v002_switch FROM @read_sql;
EXECUTE ataf_mah_v002_switch;
DEALLOCATE PREPARE ataf_mah_v002_switch;

SET @read_sql := IF(
  @start_col IS NULL,
  'SELECT 1',
  CONCAT(
    'SET @c_start := (SELECT `', @start_col,
    '` FROM `oeffnungszeit` WHERE `OeffnungszeitID` = 136550 LIMIT 1)'
  )
);
PREPARE ataf_mah_v002_cstart FROM @read_sql;
EXECUTE ataf_mah_v002_cstart;
DEALLOCATE PREPARE ataf_mah_v002_cstart;

SET @read_sql := IF(
  @end_col IS NULL,
  'SELECT 1',
  CONCAT(
    'SET @c_end := (SELECT `', @end_col,
    '` FROM `oeffnungszeit` WHERE `OeffnungszeitID` = 136550 LIMIT 1)'
  )
);
PREPARE ataf_mah_v002_cend FROM @read_sql;
EXECUTE ataf_mah_v002_cend;
DEALLOCATE PREPARE ataf_mah_v002_cend;

SET @do_split := IF(
  @switch_day IS NOT NULL
    AND @c_start IS NOT NULL
    AND @c_end IS NOT NULL
    AND @c_start <= @switch_day
    AND @c_end >= @switch_day,
  1,
  0
);

SET @before_end := DATE_SUB(@switch_day, INTERVAL 1 DAY);
SET @after_start := DATE_ADD(@switch_day, INTERVAL 1 DAY);

SET @has_standort := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'StandortID'
);
SET @has_scope := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'scope_id'
);

SET @trim_sql := IF(
  @do_split = 0,
  'SELECT 1',
  IF(
    @c_start <= @before_end,
    IF(
      @has_de > 0 AND @has_en > 0,
      CONCAT(
        'UPDATE `oeffnungszeit` SET `Endedatum` = ''', @before_end,
        ''', `end_date` = ''', @before_end,
        ''' WHERE `OeffnungszeitID` = 136550'
      ),
      IF(
        @has_en > 0,
        CONCAT(
          'UPDATE `oeffnungszeit` SET `end_date` = ''', @before_end,
          ''' WHERE `OeffnungszeitID` = 136550'
        ),
        CONCAT(
          'UPDATE `oeffnungszeit` SET `Endedatum` = ''', @before_end,
          ''' WHERE `OeffnungszeitID` = 136550'
        )
      )
    ),
    'DELETE FROM `oeffnungszeit` WHERE `OeffnungszeitID` = 136550'
  )
);
PREPARE ataf_mah_v002_trim FROM @trim_sql;
EXECUTE ataf_mah_v002_trim;
DEALLOCATE PREPARE ataf_mah_v002_trim;

SET @ins_sql := IF(
  @do_split = 0 OR @after_start > @c_end,
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
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136551,347,''', @after_start, ''',''', @c_end, ''',1,0,127,''00:00:00'',''08:00:00'',',
      '''00:00:00'',''18:00:00'',''00:25:00'',0,1,',
      '''ZMSKVR-933 ZMSKVR-934 Schalter C after scope-switch day'',0,1,0,30,NOW(),',
      '347,''', @after_start, ''',''', @c_end, ''',1,0,127,''00:00:00'',''08:00:00'',',
      '''00:00:00'',''18:00:00'',''00:25:00'',0,1,',
      '''ZMSKVR-933 ZMSKVR-934 Schalter C after scope-switch day'',0,1,0,30,NOW())'
    ),
    IF(
      @has_scope > 0,
      CONCAT(
        'INSERT IGNORE INTO `oeffnungszeit` (',
        '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
        '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
        '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
        '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
        '(136551,347,''', @after_start, ''',''', @c_end, ''',1,0,127,''00:00:00'',''08:00:00'',',
        '''00:00:00'',''18:00:00'',''00:25:00'',0,1,',
        '''ZMSKVR-933 ZMSKVR-934 Schalter C after scope-switch day'',0,1,0,30,NOW())'
      ),
      CONCAT(
        'INSERT IGNORE INTO `oeffnungszeit` (',
        '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
        '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
        '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
        '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
        '(136551,347,''', @after_start, ''',''', @c_end, ''',1,0,127,''00:00:00'',''08:00:00'',',
        '''00:00:00'',''18:00:00'',''00:25:00'',0,1,',
        '''ZMSKVR-933 ZMSKVR-934 Schalter C after scope-switch day'',0,1,0,30,NOW())'
      )
    )
  )
);
PREPARE ataf_mah_v002_ins FROM @ins_sql;
EXECUTE ataf_mah_v002_ins;
DEALLOCATE PREPARE ataf_mah_v002_ins;
