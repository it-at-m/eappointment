LOCK TABLES `source` WRITE;

INSERT INTO `source` SET `source` = "unittest", `label` = "Unittest Source", `editable` = 1, `contact__name` = "BerlinOnline Stadtportal GmbH", `contact__email` = "zms@berlinonline.de";

UNLOCK TABLES;

LOCK TABLES `provider` WRITE;

INSERT INTO `provider` (`source`, `id`, `name`, `contact__city`, `contact__country`, `contact__lat`, `contact__lon`, `contact__postalCode`, `contact__region`, `contact__street`, `contact__streetNumber`, `link`, `data`) VALUES 
('unittest', '9999998', 'Unittest Source Dienstleister', 'Berlin', 'Germany', '11.1111', '22.2222', '10178', 'Berlin', 'Alte Jakobstraße', 105, 'https://www.berlinonline.de', '{"json":"data"}'), 
('unittest', '9999999', 'Unittest Source Dienstleister 2', 'Berlin', 'Germany', '33.3333', '44.4444', '10178', 'Berlin', 'Alte Jakobstraße', 106, 'https://www.berlinonline.de', '{"json":"data","key":"value"}');

UNLOCK TABLES;

LOCK TABLES `request` WRITE;

INSERT INTO `request` (`source`,`id`,`name`,`link`,`group`,`data`) VALUES 
('unittest','9999998','Unittest Source Dienstleistung','https://www.berlinonline.de','Unittests','{"json":"data"}'),
('unittest','9999999','Unittest Source Dienstleistung 2','https://www.berlinonline.de','Unittests','{"json":"data","key":"value"}');

UNLOCK TABLES;

LOCK TABLES `request_provider` WRITE;

INSERT INTO `request_provider` (`source`,`request__id`,`provider__id`,`slots`) VALUES 
('unittest','9999998','9999998',2),
('unittest','9999998','9999999',1),
('unittest','9999999','9999999',1);

UNLOCK TABLES;


UPDATE `buerger` SET `bestaetigt` = 1 WHERE `BuergerID` IN (10118, 10114, 10030);

LOCK TABLES `closures` WRITE;

DELETE FROM closures WHERE (StandortID IN (58,59) AND year=2025 AND month=9 AND day IN (3,4));

INSERT INTO closures (StandortID, year, month, day, updateTimestamp)
VALUES
  (58, 2025, 9, 3, '2025-09-01 12:00:00'),
  (59, 2025, 9, 4, '2025-09-01 12:00:00');

UNLOCK TABLES;

/* ------------------------------------------------------------------
   Test-Daten RequestVariant
-------------------------------------------------------------------*/
LOCK TABLES `request_variant` WRITE;
DELETE FROM `request_variant`;

INSERT INTO `request_variant` (`id`, `name`) VALUES
  (2, 'B – Anmeldung'),
  (1, 'A – Abmeldung'),
  (3, 'C – Änderungsmeldung');

UNLOCK TABLES;

/* ------------------------------------------------------------------
   Test-Daten für OverviewCalendar
   - Minimal: Standorte + initiale Buchungen
-------------------------------------------------------------------*/
LOCK TABLES
    `standort` WRITE,
    `overview_calendar` WRITE;

INSERT INTO `standort`
(`StandortID`, `Bezeichnung`, `standortkuerzel`, `wartenrhinweis`, `aufrufanzeigetext`, `source`)
VALUES
    (101, 'Teststelle 101', 'T101', '', '', 'zms'),
    (102, 'Teststelle 102', 'T102', '', '', 'zms'),
    (1301, 'Teststelle 1301', 'T1301', '', '', 'zms')
    ON DUPLICATE KEY UPDATE
                         `Bezeichnung`     = VALUES(`Bezeichnung`),
                         `standortkuerzel` = VALUES(`standortkuerzel`);

DELETE FROM `overview_calendar`
WHERE `scope_id` IN (101,102,1301);

INSERT INTO `overview_calendar`
(`scope_id`, `process_id`, `status`, `starts_at`, `ends_at`, `updated_at`)
VALUES
    (102, 900100, 'confirmed', '2025-05-14 09:00:00', '2025-05-14 09:05:00', '2025-05-05 00:00:00'),
    (102, 900101, 'confirmed', '2025-05-14 10:00:00', '2025-05-14 10:05:00', '2025-05-05 00:00:00'),
    (102, 900102, 'cancelled', '2025-05-14 11:00:00', '2025-05-14 11:05:00', '2025-05-05 00:00:00');
UNLOCK TABLES;

/* ---------------------------------------------------------------
   Seeds für OverallCalendarRead
   - Standort 2102
   - Öffnungszeiten (Availability) für 2025-05-14
   - Buchungen (overview_calendar) für 2025-05-14
---------------------------------------------------------------- */

LOCK TABLES
  `standort` WRITE,
  `oeffnungszeit` WRITE,
  `overview_calendar` WRITE;

INSERT INTO `standort`
(`StandortID`, `Bezeichnung`,           `standortkuerzel`, `wartenrhinweis`, `aufrufanzeigetext`, `source`)
VALUES
    (2102,        'Teststelle 2102 (API)', 'T2102',           '',               '',                  'zms')
    ON DUPLICATE KEY UPDATE
                         `Bezeichnung`     = VALUES(`Bezeichnung`),
                         `standortkuerzel` = VALUES(`standortkuerzel`);

DELETE FROM `oeffnungszeit`     WHERE `StandortID` = 2102;
DELETE FROM `overview_calendar` WHERE `scope_id`   = 2102;

INSERT INTO `oeffnungszeit`
(`OeffnungszeitID`, `StandortID`, `Startdatum`,   `Endedatum`,
 `allexWochen`, `jedexteWoche`, `Wochentag`,
 `Anfangszeit`, `Terminanfangszeit`, `Endzeit`,   `Terminendzeit`,
 `Timeslot`,
 `Anzahlarbeitsplaetze`, `Anzahlterminarbeitsplaetze`,
 `kommentar`,                 `reduktionTermineImInternet`, `erlaubemehrfachslots`,
 `reduktionTermineCallcenter`, `Offen_ab`, `Offen_bis`, `updateTimestamp`)
VALUES
    (92102,
     2102,
     '2025-05-14', '2025-05-14',
     0, 1, 32,
     '09:00:00', '09:00:00', '11:00:00', '11:00:00',
     '00:05:00',
     3, 3,
     'Unit-Test Availability 2102',
     0, 1,
     0, 0, 0,
     '2025-05-05 00:00:00');

INSERT INTO `overview_calendar`
(`scope_id`, `process_id`, `status`,     `starts_at`,              `ends_at`,                `updated_at`)
VALUES
    (2102,       220001,       'confirmed',  '2025-05-14 09:30:00',    '2025-05-14 09:45:00',    '2025-05-05 00:00:00'),
    (2102,       220002,       'confirmed',  '2025-05-14 10:15:00',    '2025-05-14 10:30:00',    '2025-05-05 00:00:00'),
    (2102,       220003,       'cancelled',  '2025-05-14 10:45:00',    '2025-05-14 11:00:00',    '2025-05-05 00:00:00');

UNLOCK TABLES;
