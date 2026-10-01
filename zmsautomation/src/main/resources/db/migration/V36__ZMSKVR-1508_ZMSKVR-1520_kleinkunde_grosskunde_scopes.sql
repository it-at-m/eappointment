-- ZMSKVR-1508 / ZMSKVR-1520: bookable Kleinkunde and Großkunde appointments.
--
-- V23 already has the source-zms catalog (the DLDB import does not delete source zms):
--   service 6 / location 5 / variant 6 — Planeinsicht Kleinkunde, 30 minutes
--   service 7 / location 6 / variant 7 — Planeinsicht Gewerbe (Großkunde), 210 minutes
-- Neither provider had a Standort, so the citizen calendar had no slots.
--
-- A 210-minute appointment needs a long window. Stay on today only while four
-- hours remain; otherwise the next day is open 00:05–23:55. CURDATE()/CURTIME()
-- follow zms-db TZ=Europe/Berlin and are evaluated on the test run.

INSERT INTO `standort` (`StandortID`, `BehoerdenID`, `InfoDienstleisterID`, `Hinweis`, `Bezeichnung`, `Adresse`, `Stadtplanlink`, `Bearbeitungszeit`, `Kennung`, `Termine_ab`, `Termine_bis`, `smswarteschlange`, `smswmsbestaetigung`, `smsbenachrichtigungsfrist`, `smsbenachrichtigungstext`, `smsbestaetigungstext`, `wartenrsperre`, `wartenrhinweis`, `notruffunktion`, `notrufausgeloest`, `notrufinitiierung`, `notrufantwort`, `emailPflichtfeld`, `anmerkungPflichtfeld`, `anmerkungLabel`, `telefonPflichtfeld`, `standortinfozeile`, `standortkuerzel`, `aufrufanzeigetext`, `reservierungsdauer`, `anzahlwiederaufruf`, `startwartenr`, `endwartenr`, `letztewartenr`, `wartenrdatum`, `mehrfachtermine`, `schreibschutz`, `ohnestatistik`, `smskioskangebotsfrist`, `emailstandortadmin`, `wartenummernkontingent`, `vergebenewartenummern`, `kundenbefragung`, `kundenbef_label`, `kundenbef_emailtext`, `telefonaktiviert`, `virtuellesachbearbeiterzahl`, `datumvirtuellesachbearbeiterzahl`, `smsnachtrag`, `loeschdauer`, `updateTimestamp`, `source`, `custom_text_field_label`, `custom_text_field_active`, `custom_text_field_required`, `admin_mail_on_appointment`, `admin_mail_on_deleted`, `admin_mail_on_updated`, `admin_mail_on_mail_sent`, `appointments_per_mail`, `whitelisted_mails`, `slots_per_appointment`, `info_for_appointment`, `aktivierungsdauer`, `captcha_activated_required`, `email_confirmation_activated`, `custom_text_field2_label`, `custom_text_field2_active`, `custom_text_field2_required`, `info_for_all_appointments`, `last_display_number`, `max_display_number`, `display_number_prefix`) VALUES
(378, 3, 5, '', 'Planeinsicht Kleinkunde', 'Friedenstraße 40', '', '00:05:00', 0, 0, 60, 0, 0, 10, '', '', 0, '', 1, 0, NULL, NULL, 1, 0, '', 0, 'Planeinsicht Kleinkunde', 'MSE', 'Herzlich Willkommen', 15, 0, 1, 999, 1, '2026-10-01', 1, 1, 1, 0, '', 999, 1, 0, '', '', 0, -1, '2026-10-01', 0, 15, NOW(), 'zms', '', 0, 0, 0, 0, 0, 0, 0, '', 0, '', 30, 0, 0, '', 0, 0, '', 0, 9999, ''),
(379, 3, 6, '', 'Planeinsicht Gewerbe', 'Friedenstraße 40', '', '00:05:00', 0, 0, 60, 0, 0, 10, '', '', 0, '', 1, 0, NULL, NULL, 1, 0, '', 0, 'Planeinsicht Gewerbe', 'MSE', 'Herzlich Willkommen', 15, 0, 1, 999, 1, '2026-10-01', 1, 1, 1, 0, '', 999, 1, 0, '', '', 0, -1, '2026-10-01', 0, 15, NOW(), 'zms', '', 0, 0, 0, 0, 0, 0, 0, '', 0, '', 30, 0, 0, '', 0, 0, '', 0, 9999, '');

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`) VALUES
('scope', 378, 'appointment', 'activationDuration', '30', NOW()),
('scope', 378, 'appointment', 'deallocationDuration', '15', NOW()),
('scope', 378, 'appointment', 'endInDaysDefault', '60', NOW()),
('scope', 378, 'appointment', 'multipleSlotsEnabled', '1', NOW()),
('scope', 378, 'appointment', 'reservationDuration', '15', NOW()),
('scope', 378, 'appointment', 'startInDaysDefault', '0', NOW()),
('scope', 378, 'client', 'captchaActivatedRequired', '0', NOW()),
('scope', 378, 'client', 'emailFrom', 'noreply-terminvereinbarung@muenchen.de', NOW()),
('scope', 378, 'client', 'emailRequired', '1', NOW()),
('scope', 379, 'appointment', 'activationDuration', '30', NOW()),
('scope', 379, 'appointment', 'deallocationDuration', '15', NOW()),
('scope', 379, 'appointment', 'endInDaysDefault', '60', NOW()),
('scope', 379, 'appointment', 'multipleSlotsEnabled', '1', NOW()),
('scope', 379, 'appointment', 'reservationDuration', '15', NOW()),
('scope', 379, 'appointment', 'startInDaysDefault', '0', NOW()),
('scope', 379, 'client', 'captchaActivatedRequired', '0', NOW()),
('scope', 379, 'client', 'emailFrom', 'noreply-terminvereinbarung@muenchen.de', NOW()),
('scope', 379, 'client', 'emailRequired', '1', NOW()),
('scope', 369, 'client', 'emailFrom', 'noreply-terminvereinbarung@muenchen.de', NOW()),
('scope', 371, 'client', 'emailFrom', 'noreply-terminvereinbarung@muenchen.de', NOW());

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
      '(136460,378,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1508 ZMSKVR-1520 Planeinsicht Kleinkunde'',0,60,0,60,NOW(),',
      '378,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1508 ZMSKVR-1520 Planeinsicht Kleinkunde'',0,60,0,60,NOW()),',
      '(136461,379,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1508 ZMSKVR-1520 Planeinsicht Gewerbe'',0,60,0,60,NOW(),',
      '379,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1508 ZMSKVR-1520 Planeinsicht Gewerbe'',0,60,0,60,NOW())'
    )
    WHEN @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136460,378,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1508 ZMSKVR-1520 Planeinsicht Kleinkunde'',0,60,0,60,NOW()),',
      '(136461,379,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1508 ZMSKVR-1520 Planeinsicht Gewerbe'',0,60,0,60,NOW())'
    )
    ELSE CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
      '(136460,378,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1508 ZMSKVR-1520 Planeinsicht Kleinkunde'',0,60,0,60,NOW()),',
      '(136461,379,''', @range_start, ''',''', @range_end, ''',1,0,127,''00:00:00'',''', @appt_start,
      ''',''00:00:00'',''', @appt_end, ''',''00:05:00'',0,5,',
      '''ZMSKVR-1508 ZMSKVR-1520 Planeinsicht Gewerbe'',0,60,0,60,NOW())'
    )
  END
);

PREPARE zmskvr1520_hours FROM @hours_sql;
EXECUTE zmskvr1520_hours;
DEALLOCATE PREPARE zmskvr1520_hours;
