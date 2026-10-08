-- ZMSKVR-1620 / ZMSKVR-1691: disable citizen rescheduling.
--
-- Standort 205, 208 and 211 are Ausländerbehörde counters (Behörde 64, SZE
-- Abteilung 2) and share provider 10446. The offices payload keeps whichever
-- of those scopes it sees last, so all three are disabled. Slots are only
-- opened on 205, so a clerk can book internal service 1080784 there.
-- That service stays unpublished. Citizens cannot book it from the public page.
-- Passkalender scopes 172, 184 and 342 share public provider 10502 (Reisepass).
-- They are disabled too, so a logged-in citizen can book that public service
-- and still see no Termin verschieben. 10492 stays enabled.
-- full-setup runs Flyway before bin/migrate, so the column may not exist yet.
-- Hours follow Berlin now: stay on today while four hours remain, otherwise
-- open the next day. CURDATE()/CURTIME() use zms-db TZ=Europe/Berlin.

ALTER TABLE `standort`
    ADD COLUMN IF NOT EXISTS `rebooking_disabled` int(5) NOT NULL DEFAULT 0;

UPDATE `standort`
SET `rebooking_disabled` = 1
WHERE `StandortID` IN (205, 208, 211, 172, 184, 342);

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`)
VALUES
  ('scope', 205, 'appointment', 'rebookingDisabled', '1', NOW()),
  ('scope', 208, 'appointment', 'rebookingDisabled', '1', NOW()),
  ('scope', 211, 'appointment', 'rebookingDisabled', '1', NOW()),
  ('scope', 172, 'appointment', 'rebookingDisabled', '1', NOW()),
  ('scope', 184, 'appointment', 'rebookingDisabled', '1', NOW()),
  ('scope', 342, 'appointment', 'rebookingDisabled', '1', NOW())
ON DUPLICATE KEY UPDATE
  `value` = VALUES(`value`),
  `updateTimestamp` = VALUES(`updateTimestamp`);

SET @slot_seconds := 300;
SET @latest_end := '23:55:00';
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / @slot_seconds) * @slot_seconds);

SET @start_sec := TIME_TO_SEC(@rounded_start);
SET @end_sec := TIME_TO_SEC(@latest_end);
SET @use_next_day := (@start_sec >= 24 * 3600) OR (@end_sec <= @start_sec) OR ((@end_sec - @start_sec) < 4 * 3600);

SET @appt_start := IF(@use_next_day, '00:05:00', @rounded_start);
SET @appt_end := @latest_end;

SET @range_start := IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 1 DAY), CURDATE());
SET @range_end :=
  IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY));

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
      '(136530,205,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1620 ZMSKVR-1691 SZE scope 205'',0,60,0,60,NOW(),',
      '205,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1620 ZMSKVR-1691 SZE scope 205'',0,60,0,60,NOW())'
    )
    WHEN @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136530,205,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1620 ZMSKVR-1691 SZE scope 205'',0,60,0,60,NOW())'
    )
    ELSE CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
      '(136530,205,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1620 ZMSKVR-1691 SZE scope 205'',0,60,0,60,NOW())'
    )
  END
);

PREPARE zmskvr1691_hours FROM @hours_sql;
EXECUTE zmskvr1691_hours;
DEALLOCATE PREPARE zmskvr1691_hours;
