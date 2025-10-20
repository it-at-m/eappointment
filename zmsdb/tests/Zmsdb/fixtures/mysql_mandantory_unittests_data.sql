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

/* ------------------------------------------------------------------
   Test‑Daten OverallCalendar
-------------------------------------------------------------------*/
LOCK TABLES
  	`gesamtkalender` WRITE,
  	`oeffnungszeit`  WRITE;


/* --- Scope 1300 ---------------------------------------------------*/
DELETE FROM `gesamtkalender` WHERE scope_id = 101;
INSERT INTO `gesamtkalender` (scope_id, time,`availability_id`, seat, status) VALUES
  (101, '2016-05-27 09:30:00', 1550, 1, 'free'),
  (101, '2016-05-27 09:35:00', 1550, 1, 'free');

/* --- Scope 1301 – Availability‑Test ------------------------------*/
DELETE FROM `gesamtkalender` WHERE scope_id = 1301;
DELETE FROM `oeffnungszeit`   WHERE OeffnungszeitID = 999;  /* optional */

INSERT INTO `oeffnungszeit`
  (`OeffnungszeitID`, `StandortID`, `Startdatum`, `Endedatum`,
   `allexWochen`, `jedexteWoche`, `Wochentag`,
   `Anfangszeit`, `Terminanfangszeit`, `Endzeit`, `Terminendzeit`,
   `Timeslot`,
   `Anzahlarbeitsplaetze`, `Anzahlterminarbeitsplaetze`,
   `kommentar`, `reduktionTermineImInternet`, `erlaubemehrfachslots`,
   `reduktionTermineCallcenter`, `Offen_ab`, `Offen_bis`, `updateTimestamp`)
VALUES
  (999,               
   1301,
   '2016-05-27','2016-05-27',
   0,1,32,
   '09:00:00','09:00:00','10:00:00','10:00:00',
   '00:05:00',
   2,2,
   'Unit‑Test Availability',
   0,1,0,
   0,0,
   '2025-05-05 00:00:00');

UNLOCK TABLES;


/* ------------------------------------------------------------------
   Test‑Daten NUR für OverallCalendarRead‑Controller‑Tests
   (verwendet Scope‑IDs > 2000, Availability‑IDs > 9000)
-------------------------------------------------------------------*/
LOCK TABLES
  	`gesamtkalender` WRITE;

/* ---------- Scope 2001  (5‑Min‑Raster, 3 Seats) ------------------*/
DELETE FROM `gesamtkalender` WHERE scope_id = 2001;

INSERT INTO `gesamtkalender` (`scope_id`, `availability_id`, `time`, `seat`, `status`, `process_id`, `slots`)
VALUES
  (102, 9001, '2025-05-14 09:00:00', 1, 'termin', 100001, 3),
  (102, 9001, '2025-05-14 09:05:00', 1, 'termin', 100001, null),
  (102, 9001, '2025-05-14 09:10:00', 1, 'termin', 100001, null),
  (102, 9001, '2025-05-14 09:00:00', 2, 'free', null, null),
  (102, 9001, '2025-05-14 09:05:00', 2, 'free', null, null),
  (102, 9001, '2025-05-14 09:10:00', 2, 'free', null, null);

UNLOCK TABLES;

LOCK TABLES
  	`buerger` WRITE;

UPDATE buerger
SET `status` = CASE
    WHEN `Name` = '(abgesagt)' THEN 'deleted'
    WHEN `StandortID` = 0 AND `AbholortID` = 0 THEN 'blocked'
    WHEN `vorlaeufigeBuchung` = 1 AND `bestaetigt` = 0 THEN 'reserved'
    WHEN `nicht_erschienen` != 0 THEN 'missed'
    WHEN `parked` != 0 THEN 'parked'
    WHEN `Abholer` != 0 AND `AbholortID` != 0 AND `NutzerID` = 0 THEN 'pending'
    WHEN `AbholortID` != 0 AND `NutzerID` != 0 THEN 'pickup'
    WHEN `AbholortID` = 0 AND `aufruferfolgreich` != 0 AND `NutzerID` != 0 THEN 'processing'
    WHEN `aufrufzeit` != '00:00:00' AND `NutzerID` != 0 AND `AbholortID` = 0 THEN 'called'
    WHEN `Uhrzeit` = '00:00:00' THEN 'queued'
    WHEN `vorlaeufigeBuchung` = 0 AND `bestaetigt` = 0 THEN 'preconfirmed'
    WHEN `vorlaeufigeBuchung` = 0 AND `bestaetigt` = 1 THEN 'confirmed'
    ELSE 'free'
END
WHERE status IS NULL;

UNLOCK TABLES;

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

UPDATE `buerger` SET `status` = 'confirmed' WHERE `BuergerID` IN (10118, 10114, 10030);