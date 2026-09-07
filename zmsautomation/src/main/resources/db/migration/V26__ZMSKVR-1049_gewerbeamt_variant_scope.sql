-- Flyway migration: zms Gewerbeamt variant scope (ZMSKVR-1049)
--
-- V23 already has source-zms requests 8/9 (Gewerbeanmeldung Telefon/Video) on
-- provider 7 via request_provider. Provider 7 JSON lists only parent DLDB
-- service ids (1063423, …), not 8/9 — the intern RequestNotFound case.
--
-- There was no Standort for provider 7. This adds scope 377 under Behörde 2
-- (Gewerbeamt) so intern booking can select those variants.
-- Dropdown label is provider.name + shortName: "Gewerbeamt Telefon/Video".
-- REST logs in as superuser ataf (not agent_queue, who is only Behörde 40).
--
-- IDs 373/376 already have V6 preferences without matching V5 standorts.

INSERT INTO `standort` (`StandortID`, `BehoerdenID`, `InfoDienstleisterID`, `Hinweis`, `Bezeichnung`, `Adresse`, `Stadtplanlink`, `Bearbeitungszeit`, `Kennung`, `Termine_ab`, `Termine_bis`, `smswarteschlange`, `smswmsbestaetigung`, `smsbenachrichtigungsfrist`, `smsbenachrichtigungstext`, `smsbestaetigungstext`, `wartenrsperre`, `wartenrhinweis`, `notruffunktion`, `notrufausgeloest`, `notrufinitiierung`, `notrufantwort`, `emailPflichtfeld`, `anmerkungPflichtfeld`, `anmerkungLabel`, `telefonPflichtfeld`, `standortinfozeile`, `standortkuerzel`, `aufrufanzeigetext`, `reservierungsdauer`, `anzahlwiederaufruf`, `startwartenr`, `endwartenr`, `letztewartenr`, `wartenrdatum`, `mehrfachtermine`, `schreibschutz`, `ohnestatistik`, `smskioskangebotsfrist`, `emailstandortadmin`, `wartenummernkontingent`, `vergebenewartenummern`, `kundenbefragung`, `kundenbef_label`, `kundenbef_emailtext`, `telefonaktiviert`, `virtuellesachbearbeiterzahl`, `datumvirtuellesachbearbeiterzahl`, `smsnachtrag`, `loeschdauer`, `updateTimestamp`, `source`, `custom_text_field_label`, `custom_text_field_active`, `custom_text_field_required`, `admin_mail_on_appointment`, `admin_mail_on_deleted`, `admin_mail_on_updated`, `admin_mail_on_mail_sent`, `appointments_per_mail`, `whitelisted_mails`, `slots_per_appointment`, `info_for_appointment`, `aktivierungsdauer`, `captcha_activated_required`, `email_confirmation_activated`, `custom_text_field2_label`, `custom_text_field2_active`, `custom_text_field2_required`, `info_for_all_appointments`, `last_display_number`, `max_display_number`, `display_number_prefix`) VALUES
(377, 2, 7, '', 'Gewerbeamt Varianten', 'Implerstraße 11', '', '00:12:00', 0, 0, 60, 0, 0, 10, '', '', 0, '', 1, 0, NULL, NULL, 1, 0, '', 0, 'Gewerbeamt Varianten', 'Telefon/Video', 'Herzlich Willkommen', 15, 0, 1, 999, 1, '2025-11-17', 1, 1, 1, 0, '', 999, 1, 0, '', '', 0, -1, '2025-11-17', 0, 15, NOW(), 'zms', '', 0, 0, 0, 0, 0, 0, 0, '', 0, '', 60, 0, 0, '', 0, 0, '', 0, 9999, '');

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`) VALUES
('scope', 377, 'appointment', 'activationDuration', '60', NOW()),
('scope', 377, 'appointment', 'deallocationDuration', '15', NOW()),
('scope', 377, 'appointment', 'endInDaysDefault', '60', NOW()),
('scope', 377, 'appointment', 'multipleSlotsEnabled', '1', NOW()),
('scope', 377, 'appointment', 'reservationDuration', '15', NOW()),
('scope', 377, 'appointment', 'startInDaysDefault', '0', NOW()),
('scope', 377, 'client', 'captchaActivatedRequired', '0', NOW()),
('scope', 377, 'client', 'emailFrom', 'noreply-terminvereinbarung@muenchen.de', NOW()),
('scope', 377, 'client', 'emailRequired', '1', NOW()),
('scope', 377, 'notifications', 'headsUpTime', '10', NOW()),
('scope', 377, 'pickup', 'alternateName', 'Ausgabe', NOW()),
('scope', 377, 'queue', 'callCountMax', '0', NOW()),
('scope', 377, 'queue', 'callDisplayText', 'Herzlich Willkommen', NOW()),
('scope', 377, 'queue', 'firstNumber', '1', NOW()),
('scope', 377, 'queue', 'lastNumber', '999', NOW()),
('scope', 377, 'queue', 'maxNumberContingent', '999', NOW()),
('scope', 377, 'queue', 'processingTimeAverage', '12', NOW()),
('scope', 377, 'ticketprinter', 'buttonName', 'Gewerbeamt Varianten', NOW()),
('scope', 377, 'workstation', 'emergencyRefreshInterval', '5', NOW());

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
  (136209, 377, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'ZMSKVR-1049 Gewerbeamt Varianten Öffnungszeit',
   0, 5,
   0, 60,
   NOW());
