-- Flyway migration: Ausbildung scope for Ruppertstraße (ZMSKVR-1046)
--
-- Adds a local test Standort for DLDB OfficeID 10503
-- "Bürgerbüro Ruppertstraße (Ausbildung)" under Behörde 58
-- (Bürgerbüro Ruppertstraße), mirroring Pass scope 172.
--
-- Email for Behörde 58 already exists in V7__standort_email_address_test_data.
--
-- TODO ZMSKVR-1046: Update later with prod ids (StandortID / scope, InfoDienstleisterID /
-- officeId, OeffnungszeitID) once the real Ausbildung office and scope exist on prod.

-- ---------------------------------------------------------------------------
-- Standort (scope)
-- ---------------------------------------------------------------------------
INSERT INTO `standort` (`StandortID`, `BehoerdenID`, `InfoDienstleisterID`, `Hinweis`, `Bezeichnung`, `Adresse`, `Stadtplanlink`, `Bearbeitungszeit`, `Kennung`, `Termine_ab`, `Termine_bis`, `smswarteschlange`, `smswmsbestaetigung`, `smsbenachrichtigungsfrist`, `smsbenachrichtigungstext`, `smsbestaetigungstext`, `wartenrsperre`, `wartenrhinweis`, `notruffunktion`, `notrufausgeloest`, `notrufinitiierung`, `notrufantwort`, `emailPflichtfeld`, `anmerkungPflichtfeld`, `anmerkungLabel`, `telefonPflichtfeld`, `standortinfozeile`, `standortkuerzel`, `aufrufanzeigetext`, `reservierungsdauer`, `anzahlwiederaufruf`, `startwartenr`, `endwartenr`, `letztewartenr`, `wartenrdatum`, `mehrfachtermine`, `schreibschutz`, `ohnestatistik`, `smskioskangebotsfrist`, `emailstandortadmin`, `wartenummernkontingent`, `vergebenewartenummern`, `kundenbefragung`, `kundenbef_label`, `kundenbef_emailtext`, `telefonaktiviert`, `virtuellesachbearbeiterzahl`, `datumvirtuellesachbearbeiterzahl`, `smsnachtrag`, `loeschdauer`, `updateTimestamp`, `source`, `custom_text_field_label`, `custom_text_field_active`, `custom_text_field_required`, `admin_mail_on_appointment`, `admin_mail_on_deleted`, `admin_mail_on_updated`, `admin_mail_on_mail_sent`, `appointments_per_mail`, `whitelisted_mails`, `slots_per_appointment`, `info_for_appointment`, `aktivierungsdauer`, `captcha_activated_required`, `email_confirmation_activated`, `custom_text_field2_label`, `custom_text_field2_active`, `custom_text_field2_required`, `info_for_all_appointments`, `last_display_number`, `max_display_number`, `display_number_prefix`) VALUES
(372, 58, 10503, 'Eingang B - EG - Wartebereich 04', 'Bürgerbüro Ruppertstraße (Ausbildung)', 'Ruppertstraße 19', '', '00:12:00', 0, 0, 42, 0, 0, 10, '', '', 0, '', 1, 0, NULL, NULL, 1, 0, '', 0, 'Bürgerbüro Ruppertstraße (Ausbildung)', 'Ausbildung', 'Herzlich Willkommen', 15, 3, 1, 999, 1, '2025-11-17', 1, 1, 1, 0, '', 999, 1, 0, '', '', 1, -1, '2025-11-17', 0, 5, NOW(), 'dldb', 'Zusätzliche Bemerkungen', 1, 1, 0, 0, 0, 0, 3, '', 0, 'Hinweis zum Passfoto: Für Ausweisdokumente benötigen wir ein digitales, zertifiziertes Passfoto. Es muss in einem zertifizierten Fotostudio oder Drogeriemarkt aufgenommen werden. Wir rufen das Foto über einen Code ab. Alternativ können Sie im Bürgerbüro gegen Gebühr ein Foto-Terminal nutzen. Diese Foto-Terminals sind jedoch nicht für Fotos von Babys und Kindern unter 6 Jahren geeignet.\r\n\r\n', 30, 0, 0, '', 1, 0, 'Hinweis zur Terminvergabe: Von Montag bis Freitag schalten wir jeweils zwischen 7 und 8 Uhr tagesaktuelle Termine frei. \r\nTermine für die kommende Woche folgen zwischen 9 und 11 Uhr. Langfristige Termine (in 6 Wochen beziehungsweise 42 Tagen) \r\nwerden über Nacht freigeschaltet. Schauen Sie an allen Bürgerbüro-Standorten nach freien Terminen.', 0, 9999, '');

-- ---------------------------------------------------------------------------
-- Preferences (cloned from scope 172 / Pass WB04)
-- ---------------------------------------------------------------------------
INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`) VALUES
('scope', 372, 'appointment', 'activationDuration', '30', NOW()),
('scope', 372, 'appointment', 'deallocationDuration', '5', NOW()),
('scope', 372, 'appointment', 'endInDaysDefault', '42', NOW()),
('scope', 372, 'appointment', 'infoForAllAppointments', 'Hinweis zur Terminvergabe: Von Montag bis Freitag schalten wir jeweils zwischen 7 und 8 Uhr tagesaktuelle Termine frei. \r\nTermine für die kommende Woche folgen zwischen 9 und 11 Uhr. Langfristige Termine (in 6 Wochen beziehungsweise 42 Tagen) \r\nwerden über Nacht freigeschaltet. Schauen Sie an allen Bürgerbüro-Standorten nach freien Terminen.', NOW()),
('scope', 372, 'appointment', 'infoForAppointment', 'Hinweis zum Passfoto: Für Ausweisdokumente benötigen wir ein digitales, zertifiziertes Passfoto. Es muss in einem zertifizierten Fotostudio oder Drogeriemarkt aufgenommen werden. Wir rufen das Foto über einen Code ab. Alternativ können Sie im Bürgerbüro gegen Gebühr ein Foto-Terminal nutzen. Diese Foto-Terminals sind jedoch nicht für Fotos von Babys und Kindern unter 6 Jahren geeignet.\r\n\r\n', NOW()),
('scope', 372, 'appointment', 'multipleSlotsEnabled', '1', NOW()),
('scope', 372, 'appointment', 'reservationDuration', '15', NOW()),
('scope', 372, 'client', 'captchaActivatedRequired', '0', NOW()),
('scope', 372, 'client', 'customTextfieldActivated', '1', NOW()),
('scope', 372, 'client', 'customTextfieldLabel', 'Zusätzliche Bemerkungen', NOW()),
('scope', 372, 'client', 'emailFrom', 'noreply-terminvereinbarung@muenchen.de', NOW()),
('scope', 372, 'client', 'emailRequired', '1', NOW()),
('scope', 372, 'client', 'telephoneActivated', '1', NOW()),
('scope', 372, 'notifications', 'headsUpTime', '10', NOW()),
('scope', 372, 'pickup', 'alternateName', 'Ausgabe', NOW()),
('scope', 372, 'queue', 'callCountMax', '3', NOW()),
('scope', 372, 'queue', 'callDisplayText', 'Herzlich Willkommen', NOW()),
('scope', 372, 'queue', 'displayNumberPrefix', 'M', NOW()),
('scope', 372, 'queue', 'firstNumber', '1', NOW()),
('scope', 372, 'queue', 'lastNumber', '999', NOW()),
('scope', 372, 'queue', 'maxNumberContingent', '999', NOW()),
('scope', 372, 'queue', 'processingTimeAverage', '12', NOW()),
('scope', 372, 'queue', 'statisticsEnabled', '1', NOW()),
('scope', 372, 'ticketprinter', 'buttonName', 'Bürgerbüro Ruppertstraße (Ausbildung)', NOW()),
('scope', 372, 'workstation', 'emergencyRefreshInterval', '5', NOW());

-- ---------------------------------------------------------------------------
-- Opening hours (same pattern as V19__ZMSKVR-1124_opening_hours_for_ruppertstasse)
-- ---------------------------------------------------------------------------
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / 300) * 300);

SET @desired_end :=
  ADDTIME(@rounded_start, '06:00:00');

SET @rounded_end :=
  LEAST(@desired_end, '23:55:00');

SET @start_sec := TIME_TO_SEC(@rounded_start);
SET @end_sec := TIME_TO_SEC(@rounded_end);
SET @use_next_day := (@end_sec <= @start_sec);

SET @appt_start := IF(@use_next_day, '00:05:00', @rounded_start);
SET @appt_end :=
  IF(@use_next_day, LEAST(ADDTIME('00:05:00', '03:00:00'), '23:55:00'), @rounded_end);

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
  -- Bürgerbüro Ruppertstraße (Ausbildung) – officeId 10503
  (136206, 372, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'ZMSKVR-1046 Ausbildung Öffnungszeit',
   0, 5,
   0, 30,
   NOW());
