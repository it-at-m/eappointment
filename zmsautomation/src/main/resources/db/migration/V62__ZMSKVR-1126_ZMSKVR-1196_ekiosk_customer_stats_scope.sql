-- ZMSKVR-1126 / ZMSKVR-1196: dedicated scope for E-Kiosk Kundenstatistik ATAF.
--
-- Ticketprinter walk-ins set is_ticketprinter=1. Finishing as erschienen writes
-- buergerarchiv immediately (no daily cron). This empty scope keeps counts
-- deterministic for GUI/Excel and GET /warehouse/clientscope/{id}/{day}/.
--
-- Spontankunden hours: appointment_start_time 00:00:00 → type openinghours.
-- ohnestatistik=1 skips the finish service form; archive still receives the flag.
-- CURDATE() follows zms-db TZ=Europe/Berlin.

INSERT INTO `standort` (`StandortID`, `BehoerdenID`, `InfoDienstleisterID`, `Hinweis`, `Bezeichnung`, `Adresse`, `Stadtplanlink`, `Bearbeitungszeit`, `Kennung`, `Termine_ab`, `Termine_bis`, `smswarteschlange`, `smswmsbestaetigung`, `smsbenachrichtigungsfrist`, `smsbenachrichtigungstext`, `smsbestaetigungstext`, `wartenrsperre`, `wartenrhinweis`, `notruffunktion`, `notrufausgeloest`, `notrufinitiierung`, `notrufantwort`, `emailPflichtfeld`, `anmerkungPflichtfeld`, `anmerkungLabel`, `telefonPflichtfeld`, `standortinfozeile`, `standortkuerzel`, `aufrufanzeigetext`, `reservierungsdauer`, `anzahlwiederaufruf`, `startwartenr`, `endwartenr`, `letztewartenr`, `wartenrdatum`, `mehrfachtermine`, `schreibschutz`, `ohnestatistik`, `smskioskangebotsfrist`, `emailstandortadmin`, `wartenummernkontingent`, `vergebenewartenummern`, `kundenbefragung`, `kundenbef_label`, `kundenbef_emailtext`, `telefonaktiviert`, `virtuellesachbearbeiterzahl`, `datumvirtuellesachbearbeiterzahl`, `smsnachtrag`, `loeschdauer`, `updateTimestamp`, `source`, `custom_text_field_label`, `custom_text_field_active`, `custom_text_field_required`, `admin_mail_on_appointment`, `admin_mail_on_deleted`, `admin_mail_on_updated`, `admin_mail_on_mail_sent`, `appointments_per_mail`, `whitelisted_mails`, `slots_per_appointment`, `info_for_appointment`, `aktivierungsdauer`, `captcha_activated_required`, `email_confirmation_activated`, `custom_text_field2_label`, `custom_text_field2_active`, `custom_text_field2_required`, `info_for_all_appointments`, `last_display_number`, `max_display_number`, `display_number_prefix`) VALUES
(381, 49, 1060068, '', 'ATAF E-Kiosk Kundenstatistik', 'Orleansstraße 50', '', '00:12:00', 0, 0, 30, 0, 0, 10, '', '', 0, '', 1, 0, NULL, NULL, 0, 0, '', 0, 'ZMSKVR-1126', 'EKiosk', 'Herzlich Willkommen', 15, 0, 1, 999, 1, CURDATE(), 0, 0, 1, 0, '', 999, 1, 0, '', '', 0, -1, CURDATE(), 0, 15, NOW(), 'dldb', '', 0, 0, 0, 0, 0, 0, 0, '', 0, '', 30, 0, 0, '', 0, 0, '', 0, 9999, '');

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`) VALUES
('scope', 381, 'appointment', 'activationDuration', '30', NOW()),
('scope', 381, 'appointment', 'deallocationDuration', '5', NOW()),
('scope', 381, 'appointment', 'endInDaysDefault', '30', NOW()),
('scope', 381, 'appointment', 'multipleSlotsEnabled', '0', NOW()),
('scope', 381, 'appointment', 'reservationDuration', '15', NOW()),
('scope', 381, 'appointment', 'startInDaysDefault', '0', NOW()),
('scope', 381, 'client', 'captchaActivatedRequired', '0', NOW()),
('scope', 381, 'client', 'emailFrom', 'noreply-terminvereinbarung@muenchen.de', NOW()),
('scope', 381, 'client', 'emailRequired', '0', NOW()),
('scope', 381, 'notifications', 'headsUpTime', '10', NOW()),
('scope', 381, 'pickup', 'alternateName', 'Ausgabe', NOW()),
('scope', 381, 'queue', 'callCountMax', '1', NOW()),
('scope', 381, 'queue', 'callDisplayText', 'Herzlich Willkommen', NOW()),
('scope', 381, 'queue', 'firstNumber', '1', NOW()),
('scope', 381, 'queue', 'lastNumber', '999', NOW()),
('scope', 381, 'queue', 'maxNumberContingent', '999', NOW()),
('scope', 381, 'queue', 'processingTimeAverage', '12', NOW()),
('scope', 381, 'queue', 'statisticsEnabled', '0', NOW()),
('scope', 381, 'ticketprinter', 'buttonName', 'ATAF E-Kiosk Kundenstatistik', NOW()),
('scope', 381, 'workstation', 'emergencyRefreshInterval', '5', NOW())
ON DUPLICATE KEY UPDATE
  `value` = VALUES(`value`),
  `updateTimestamp` = VALUES(`updateTimestamp`);

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
(
  136550,
  381,
  CURDATE(),
  DATE_ADD(CURDATE(), INTERVAL 14 DAY),
  1,
  0,
  127,
  '00:01:00',
  '00:00:00',
  '23:59:00',
  '00:00:00',
  '00:05:00',
  0,
  0,
  'ZMSKVR-1126 ZMSKVR-1196 E-Kiosk Spontankunden',
  0,
  0,
  0,
  30,
  NOW()
);
