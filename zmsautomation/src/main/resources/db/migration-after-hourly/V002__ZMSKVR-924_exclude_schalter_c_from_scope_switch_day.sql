-- ATAF Flyway path: db/migration-after-hourly → flyway_migration_after_hourly
--
-- V001 opens Schalter C (347 / OeffnungszeitID 136550) for CURDATE()…+14 days with one
-- internet seat. That band overlaps the V55 scope-switch day (136513 / 136514) which must
-- stay A/B-only so "second same-timestamp reserve switches Schalter" stays A↔B.
-- Split C around that day; keep 25-minute coverage on every other day for ZMSKVR-933.

SET @switch_day := (
  SELECT COALESCE(`start_date`, `Startdatum`)
  FROM `oeffnungszeit`
  WHERE `OeffnungszeitID` = 136513
  LIMIT 1
);

SET @c_start := (
  SELECT COALESCE(`start_date`, `Startdatum`)
  FROM `oeffnungszeit`
  WHERE `OeffnungszeitID` = 136550
  LIMIT 1
);

SET @c_end := (
  SELECT COALESCE(`end_date`, `Endedatum`)
  FROM `oeffnungszeit`
  WHERE `OeffnungszeitID` = 136550
  LIMIT 1
);

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

-- Nothing to do if V001 C opening or V55 switch day is missing.
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

-- End the existing C band the day before the switch day (or delete if it would be empty).
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

-- Continue C after the switch day through the original end (25-minute, one internet seat).
SET @ins_sql := IF(
  @do_split = 0 OR @after_start > @c_end,
  'SELECT 1',
  IF(
    @has_de > 0 AND @has_en > 0,
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
      @has_en > 0,
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
