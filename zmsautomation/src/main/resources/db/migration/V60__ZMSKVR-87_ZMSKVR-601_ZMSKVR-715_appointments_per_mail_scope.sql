-- ZMSKVR-87 / ZMSKVR-601 / ZMSKVR-715: appointmentsPerMail = 1 fixture scope.
--
-- Dedicated Standort 380 for Waffenbehörde provider 10521 (Europäischer
-- Feuerwaffenpass 1072587). No other ATAF feature books 10521. Limit 1 active
-- (preconfirmed/confirmed) appointment per email so the second booking hits
-- tooManyAppointmentsWithSameMail on update or preconfirm.
--
-- Hours follow Berlin now: stay on today while three hours remain, otherwise
-- open the next day. CURDATE()/CURTIME() use zms-db TZ=Europe/Berlin.

INSERT INTO `standort` (`StandortID`, `BehoerdenID`, `InfoDienstleisterID`, `Hinweis`, `Bezeichnung`, `Adresse`, `Stadtplanlink`, `Bearbeitungszeit`, `Kennung`, `Termine_ab`, `Termine_bis`, `smswarteschlange`, `smswmsbestaetigung`, `smsbenachrichtigungsfrist`, `smsbenachrichtigungstext`, `smsbestaetigungstext`, `wartenrsperre`, `wartenrhinweis`, `notruffunktion`, `notrufausgeloest`, `notrufinitiierung`, `notrufantwort`, `emailPflichtfeld`, `anmerkungPflichtfeld`, `anmerkungLabel`, `telefonPflichtfeld`, `standortinfozeile`, `standortkuerzel`, `aufrufanzeigetext`, `reservierungsdauer`, `anzahlwiederaufruf`, `startwartenr`, `endwartenr`, `letztewartenr`, `wartenrdatum`, `mehrfachtermine`, `schreibschutz`, `ohnestatistik`, `smskioskangebotsfrist`, `emailstandortadmin`, `wartenummernkontingent`, `vergebenewartenummern`, `kundenbefragung`, `kundenbef_label`, `kundenbef_emailtext`, `telefonaktiviert`, `virtuellesachbearbeiterzahl`, `datumvirtuellesachbearbeiterzahl`, `smsnachtrag`, `loeschdauer`, `updateTimestamp`, `source`, `custom_text_field_label`, `custom_text_field_active`, `custom_text_field_required`, `admin_mail_on_appointment`, `admin_mail_on_deleted`, `admin_mail_on_updated`, `admin_mail_on_mail_sent`, `appointments_per_mail`, `whitelisted_mails`, `slots_per_appointment`, `info_for_appointment`, `aktivierungsdauer`, `captcha_activated_required`, `email_confirmation_activated`, `custom_text_field2_label`, `custom_text_field2_active`, `custom_text_field2_required`, `info_for_all_appointments`, `last_display_number`, `max_display_number`, `display_number_prefix`) VALUES
(380, 91, 10521, '', 'Waffenbehörde Mail-Limit', 'Ruppertstraße 19', '', '00:12:00', 0, 0, 60, 0, 0, 10, '', '', 0, '', 1, 0, NULL, NULL, 1, 0, '', 0, 'Waffenbehörde Mail-Limit', 'Mail-Limit', 'Herzlich Willkommen', 15, 0, 1, 999, 1, '2025-11-17', 1, 1, 1, 0, '', 999, 1, 0, '', '', 0, -1, '2025-11-17', 0, 15, NOW(), 'dldb', '', 0, 0, 0, 0, 0, 0, 1, '', 0, '', 30, 0, 1, '', 0, 0, '', 0, 9999, '');

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`) VALUES
('scope', 380, 'appointment', 'activationDuration', '30', NOW()),
('scope', 380, 'appointment', 'deallocationDuration', '15', NOW()),
('scope', 380, 'appointment', 'endInDaysDefault', '60', NOW()),
('scope', 380, 'appointment', 'multipleSlotsEnabled', '1', NOW()),
('scope', 380, 'appointment', 'reservationDuration', '15', NOW()),
('scope', 380, 'appointment', 'startInDaysDefault', '0', NOW()),
('scope', 380, 'client', 'appointmentsPerMail', '1', NOW()),
('scope', 380, 'client', 'captchaActivatedRequired', '0', NOW()),
('scope', 380, 'client', 'emailConfirmationActivated', '1', NOW()),
('scope', 380, 'client', 'emailFrom', 'noreply-terminvereinbarung@muenchen.de', NOW()),
('scope', 380, 'client', 'emailRequired', '1', NOW()),
('scope', 380, 'notifications', 'headsUpTime', '10', NOW()),
('scope', 380, 'pickup', 'alternateName', 'Ausgabe', NOW()),
('scope', 380, 'queue', 'callCountMax', '0', NOW()),
('scope', 380, 'queue', 'callDisplayText', 'Herzlich Willkommen', NOW()),
('scope', 380, 'queue', 'firstNumber', '1', NOW()),
('scope', 380, 'queue', 'lastNumber', '999', NOW()),
('scope', 380, 'queue', 'maxNumberContingent', '999', NOW()),
('scope', 380, 'queue', 'processingTimeAverage', '12', NOW()),
('scope', 380, 'ticketprinter', 'buttonName', 'Waffenbehörde Mail-Limit', NOW()),
('scope', 380, 'workstation', 'emergencyRefreshInterval', '5', NOW())
ON DUPLICATE KEY UPDATE
  `value` = VALUES(`value`),
  `updateTimestamp` = VALUES(`updateTimestamp`);

SET @slot_seconds := 300;
SET @latest_end := '23:55:00';
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / @slot_seconds) * @slot_seconds);

SET @start_sec := TIME_TO_SEC(@rounded_start);
SET @end_sec := TIME_TO_SEC(@latest_end);
SET @use_next_day := (@start_sec >= 24 * 3600) OR (@end_sec <= @start_sec) OR ((@end_sec - @start_sec) < 3 * 3600);

SET @appt_start := IF(@use_next_day, '00:05:00', @rounded_start);
SET @appt_end := @latest_end;

SET @range_start := IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 1 DAY), CURDATE());
SET @range_end :=
  IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY));

INSERT IGNORE INTO `oeffnungszeit`
(
  `OeffnungszeitID`,
  `StandortID`,
  `Startdatum`,
  `Endedatum`,
  `allexWochen`,
  `jedexteWoche`,
  `Wochentag`,
  `Anfangszeit`,
  `Terminanfangszeit`,
  `Endzeit`,
  `Terminendzeit`,
  `Timeslot`,
  `Anzahlarbeitsplaetze`,
  `Anzahlterminarbeitsplaetze`,
  `kommentar`,
  `reduktionTermineImInternet`,
  `erlaubemehrfachslots`,
  `Offen_ab`,
  `Offen_bis`,
  `updateTimestamp`
)
VALUES
  (136541, 380, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'ZMSKVR-87 ZMSKVR-601 ZMSKVR-715 appointmentsPerMail scope 380',
   0, 5,
   0, 60,
   NOW());
