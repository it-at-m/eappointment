-- ZMSKVR-1457 / ZMSKVR-1524: days stay gray when the appointment no longer fits.
--
-- Scope 9901457 (Muster Kalender) is only for this test. Tomorrow 10:00–10:10
-- is two 5-minute slots (the end time is exclusive). A 5-minute service fits
-- once or twice. Three of them, the 15-minute service, or the 5+15 combination
-- do not. multiple slots are allowed, so the calendar asks for the real length
-- instead of treating every request as one slot.

INSERT INTO `provider` (
  `source`, `id`, `name`, `contact__city`, `contact__country`, `contact__lat`, `contact__lon`,
  `contact__postalCode`, `contact__region`, `contact__street`, `contact__streetNumber`, `link`, `data`, `display_name`
) VALUES (
  'zms', '9901457', 'Muster Kalender', 'München', 'Germany', 48.137, 11.575,
  '80331', 'Bayern', 'Musterweg', '1', '', '{"public":true}', 'Muster Kalender'
);

INSERT INTO `request` (`source`, `id`, `name`, `link`, `group`, `data`) VALUES
(
  'zms', '9901451', 'Muster Kurz', '', 'Muster',
  '{"public":true,"maxQuantity":3,"combinable":["9901452","9901453"],"showOnStartPage":true}'
),
(
  'zms', '9901452', 'Muster Zusatz', '', 'Muster',
  '{"public":true,"maxQuantity":1,"combinable":["9901451"],"showOnStartPage":true}'
),
(
  'zms', '9901453', 'Muster Lang', '', 'Muster',
  '{"public":true,"maxQuantity":1,"combinable":["9901451"],"showOnStartPage":true}'
);

INSERT INTO `request_provider` (
  `source`, `request__id`, `provider__id`, `slots`, `bookable`, `max_quantity`, `public_visibility`
) VALUES
  ('zms', '9901451', '9901457', 1, 1, 3, 1),
  ('zms', '9901452', '9901457', 1, 1, 1, 1),
  ('zms', '9901453', '9901457', 3, 1, 1, 1);

INSERT INTO `standort` (`StandortID`, `BehoerdenID`, `InfoDienstleisterID`, `Hinweis`, `Bezeichnung`, `Adresse`, `Stadtplanlink`, `Bearbeitungszeit`, `Kennung`, `Termine_ab`, `Termine_bis`, `smswarteschlange`, `smswmsbestaetigung`, `smsbenachrichtigungsfrist`, `smsbenachrichtigungstext`, `smsbestaetigungstext`, `wartenrsperre`, `wartenrhinweis`, `notruffunktion`, `notrufausgeloest`, `notrufinitiierung`, `notrufantwort`, `emailPflichtfeld`, `anmerkungPflichtfeld`, `anmerkungLabel`, `telefonPflichtfeld`, `standortinfozeile`, `standortkuerzel`, `aufrufanzeigetext`, `reservierungsdauer`, `anzahlwiederaufruf`, `startwartenr`, `endwartenr`, `letztewartenr`, `wartenrdatum`, `mehrfachtermine`, `schreibschutz`, `ohnestatistik`, `smskioskangebotsfrist`, `emailstandortadmin`, `wartenummernkontingent`, `vergebenewartenummern`, `kundenbefragung`, `kundenbef_label`, `kundenbef_emailtext`, `telefonaktiviert`, `virtuellesachbearbeiterzahl`, `datumvirtuellesachbearbeiterzahl`, `smsnachtrag`, `loeschdauer`, `updateTimestamp`, `source`, `custom_text_field_label`, `custom_text_field_active`, `custom_text_field_required`, `admin_mail_on_appointment`, `admin_mail_on_deleted`, `admin_mail_on_updated`, `admin_mail_on_mail_sent`, `appointments_per_mail`, `whitelisted_mails`, `slots_per_appointment`, `info_for_appointment`, `aktivierungsdauer`, `captcha_activated_required`, `email_confirmation_activated`, `custom_text_field2_label`, `custom_text_field2_active`, `custom_text_field2_required`, `info_for_all_appointments`, `last_display_number`, `max_display_number`, `display_number_prefix`) VALUES
(9901457, 40, 9901457, '', 'Muster Kalender', 'Musterweg 1', '', '00:05:00', 0, 0, 14, 0, 0, 10, '', '', 0, '', 1, 0, NULL, NULL, 1, 0, '', 0, 'Muster Kalender', 'Muster', 'Herzlich Willkommen', 15, 0, 1, 999, 1, '2025-11-17', 1, 1, 1, 0, '', 999, 1, 0, '', '', 0, -1, '2025-11-17', 0, 15, NOW(), 'zms', '', 0, 0, 0, 0, 0, 0, 0, '', 6, '', 60, 0, 0, '', 0, 0, '', 0, 9999, '');

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`) VALUES
('scope', 9901457, 'appointment', 'activationDuration', '60', NOW()),
('scope', 9901457, 'appointment', 'deallocationDuration', '15', NOW()),
('scope', 9901457, 'appointment', 'endInDaysDefault', '14', NOW()),
('scope', 9901457, 'appointment', 'multipleSlotsEnabled', '1', NOW()),
('scope', 9901457, 'appointment', 'reservationDuration', '15', NOW()),
('scope', 9901457, 'appointment', 'startInDaysDefault', '0', NOW()),
('scope', 9901457, 'client', 'captchaActivatedRequired', '0', NOW()),
('scope', 9901457, 'client', 'emailFrom', 'noreply-terminvereinbarung@muenchen.de', NOW()),
('scope', 9901457, 'client', 'emailRequired', '1', NOW()),
('scope', 9901457, 'client', 'slotsPerAppointment', '6', NOW());

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
      '(136457,9901457,''', @day, ''',''', @day, ''',1,0,127,''00:00:00'',''10:00:00'',',
      '''00:00:00'',''10:10:00'',''00:05:00'',0,1,',
      '''ZMSKVR-1457 ZMSKVR-1524 Muster Kalender'',0,5,0,14,NOW(),',
      '9901457,''', @day, ''',''', @day, ''',1,0,127,''00:00:00'',''10:00:00'',',
      '''00:00:00'',''10:10:00'',''00:05:00'',0,1,',
      '''ZMSKVR-1457 ZMSKVR-1524 Muster Kalender'',0,5,0,14,NOW())'
    )
    WHEN @has_en > 0 THEN CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,`every_x_weeks`,`every_other_week`,`weekday`,',
      '`start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,`time_slot`,',
      '`workstation_count`,`appointment_workstation_count`,`comment`,`internet_reduction`,',
      '`multiple_slots_allowed`,`open_from_days`,`open_until_days`,`updated_at`) VALUES ',
      '(136457,9901457,''', @day, ''',''', @day, ''',1,0,127,''00:00:00'',''10:00:00'',',
      '''00:00:00'',''10:10:00'',''00:05:00'',0,1,',
      '''ZMSKVR-1457 ZMSKVR-1524 Muster Kalender'',0,5,0,14,NOW())'
    )
    ELSE CONCAT(
      'INSERT IGNORE INTO `oeffnungszeit` (',
      '`OeffnungszeitID`,`StandortID`,`Startdatum`,`Endedatum`,`allexWochen`,`jedexteWoche`,`Wochentag`,',
      '`Anfangszeit`,`Terminanfangszeit`,`Endzeit`,`Terminendzeit`,`Timeslot`,`Anzahlarbeitsplaetze`,',
      '`Anzahlterminarbeitsplaetze`,`kommentar`,`reduktionTermineImInternet`,`erlaubemehrfachslots`,',
      '`Offen_ab`,`Offen_bis`,`updateTimestamp`) VALUES ',
      '(136457,9901457,''', @day, ''',''', @day, ''',1,0,127,''00:00:00'',''10:00:00'',',
      '''00:00:00'',''10:10:00'',''00:05:00'',0,1,',
      '''ZMSKVR-1457 ZMSKVR-1524 Muster Kalender'',0,5,0,14,NOW())'
    )
  END
);

PREPARE zmskvr1524_hours FROM @hours_sql;
EXECUTE zmskvr1524_hours;
DEALLOCATE PREPARE zmskvr1524_hours;
