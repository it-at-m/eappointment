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

-- Fix ProcessReserveTest and ProcessFreeTest for QUERY_CANCEL_AVAILABILITY_AFTER_BOOKABLE:
-- Set open_until_days = 60 for availability 94678 to match the expected endInDays: 60 in the test fixtures
UPDATE `oeffnungszeit` SET `open_until_days` = 60 WHERE `OeffnungszeitID` = 94678;

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

/* ------------------------------------------------------------------
   Test-Daten OverviewCalendarTest, OverallCalendarRead
-------------------------------------------------------------------*/

LOCK TABLES `standort` WRITE, `oeffnungszeit` WRITE, `overview_calendar` WRITE, `buerger` WRITE;

INSERT IGNORE INTO `standort`
  (`StandortID`,`Bezeichnung`,`standortkuerzel`,`wartenrhinweis`,`aufrufanzeigetext`)
VALUES
  (65001,'UT Scope 65001','T65001','', ''),
  (65002,'UT Scope 65002','T65002','', ''),
  (65202,'UT Scope 65202 (API)','T65202','', '');

UPDATE `standort`
SET `InfoDienstleisterID` = 9999999
WHERE `StandortID` IN (65001, 65002, 65202)
  AND ( `InfoDienstleisterID` = 0 OR `InfoDienstleisterID` IS NULL );

DELETE FROM `oeffnungszeit`     WHERE `scope_id` IN (65202);
DELETE FROM `overview_calendar` WHERE `scope_id`   IN (65001,65002,65202);

INSERT INTO `overview_calendar`
(`scope_id`,`process_id`,`status`,`starts_at`,`ends_at`,`updated_at`)
VALUES
    (65002, 965001, 'confirmed', '2025-05-14 09:00:00', '2025-05-14 09:05:00', '2025-05-05 00:00:00'),
    (65002, 965002, 'confirmed', '2025-05-14 10:00:00', '2025-05-14 10:05:00', '2025-05-05 00:00:00'),
    (65002, 965003, 'cancelled', '2025-05-14 11:00:00', '2025-05-14 11:05:00', '2025-05-05 00:00:00');

INSERT INTO `oeffnungszeit`
(`OeffnungszeitID`,`scope_id`,`start_date`,`end_date`,
 `every_x_weeks`,`every_other_week`,`weekday`,
 `start_time`,`appointment_start_time`,`end_time`,`appointment_end_time`,
 `time_slot`,
 `workstation_count`,`appointment_workstation_count`,
 `comment`,`internet_reduction`,`multiple_slots_allowed`,
 `open_from_days`,`open_until_days`,`updated_at`)
VALUES
    (965202, 65202, '2025-05-14','2025-05-14',
     0,1,32,
     '09:00:00','09:00:00','11:00:00','11:00:00',
     '00:05:00',
     3,3,
     'UT Availability 65202', 0,1,0,0, '2025-05-05 00:00:00');

INSERT INTO `overview_calendar`
(`scope_id`,`process_id`,`status`,`starts_at`,`ends_at`,`updated_at`)
VALUES
    (65202, 972201, 'confirmed', '2025-05-14 09:30:00', '2025-05-14 09:45:00', '2025-05-05 00:00:00'),
    (65202, 972202, 'confirmed', '2025-05-14 10:15:00', '2025-05-14 10:30:00', '2025-05-05 00:00:00'),
    (65202, 972203, 'cancelled', '2025-05-14 10:45:00', '2025-05-14 11:00:00', '2025-05-05 00:00:00');

INSERT IGNORE INTO `buerger` (`BuergerID`,`StandortID`,`Datum`,`Uhrzeit`,`status`,`absagecode`,`IPTimeStamp`)
VALUES
    (972201, 65202, '2025-05-14', '09:30:00', 'confirmed', 'test-auth-972201', UNIX_TIMESTAMP('2025-05-05 09:00:00')),
    (972202, 65202, '2025-05-14', '10:15:00', 'confirmed', 'test-auth-972202', UNIX_TIMESTAMP('2025-05-05 10:00:00')),
    (972203, 65202, '2025-05-14', '10:45:00', 'cancelled', 'test-auth-972203', UNIX_TIMESTAMP('2025-05-05 11:00:00'));

UPDATE `buerger` SET `displayNumber` = 'TT1201' WHERE `BuergerID` = 972201;

UNLOCK TABLES;

/* ------------------------------------------------------------------
   Test-Daten UseraccountListByRoleAndDepartmentsTest
-------------------------------------------------------------------*/

LOCK TABLES `nutzer` READ, `role` READ, `user_role` WRITE, `nutzerzuordnung` WRITE;

REPLACE INTO `nutzerzuordnung` (`nutzerid`, `behoerdenid`)
SELECT `nutzer`.`NutzerID`, 74
FROM `nutzer`
WHERE `nutzer`.`Name` = 'testuser';

INSERT IGNORE INTO `user_role` (`user_id`, `role_id`)
SELECT `nutzer`.`NutzerID`, `role`.`id`
FROM `nutzer`
JOIN `role` ON `role`.`name` = 'agent_queue'
WHERE `nutzer`.`Name` = 'testuser';

UNLOCK TABLES;

/* ------------------------------------------------------------------
   Test-Daten ProcessSearchHistoryTest
-------------------------------------------------------------------*/

LOCK TABLES `provider` WRITE, `request` WRITE, `request_provider` WRITE,
            `standort` WRITE, `buerger` WRITE, `buergeranliegen` WRITE,
            `process_search_history` WRITE;

DELETE FROM `buergeranliegen` WHERE `BuergerID` = 990029;
DELETE FROM `buerger` WHERE `BuergerID` = 990029;

DELETE FROM `request_provider`
WHERE `source` = 'unittest'
  AND `request__id` = 9999997
  AND `provider__id` = 9999997;

DELETE FROM `standort` WHERE `StandortID` = 65991;
DELETE FROM `request` WHERE `source` = 'unittest' AND `id` = 9999997;
DELETE FROM `provider` WHERE `source` = 'unittest' AND `id` = 9999997;

DELETE FROM `process_search_history`
WHERE `history_key` IN (
    SHA2('retention-old', 256),
    SHA2('retention-boundary', 256),
    SHA2('retention-new', 256)
);

INSERT INTO `provider`
(`source`,`id`,`name`,`contact__city`,`contact__country`,`contact__lat`,`contact__lon`,
 `contact__postalCode`,`contact__region`,`contact__street`,`contact__streetNumber`,`link`,`data`)
VALUES
    ('unittest',9999997,'History Test Provider','Berlin','Germany',52.5200,13.4050,
     '10178','Berlin','History-Straße','1','https://www.berlinonline.de','{"test":"process-search-history"}');

INSERT INTO `request`
(`source`,`id`,`name`,`link`,`group`,`data`)
VALUES
    ('unittest',9999997,'History Test Service','https://www.berlinonline.de',
     'ProcessSearchHistoryTest','{"test":"process-search-history"}');

INSERT INTO `request_provider`
(`source`,`request__id`,`provider__id`,`slots`)
VALUES
    ('unittest',9999997,9999997,1);

INSERT INTO `standort`
(`StandortID`,`Bezeichnung`,`standortkuerzel`,`InfoDienstleisterID`,`source`,`wartenrhinweis`,`aufrufanzeigetext`)
VALUES
    (65991,'History Test Standort','HST',9999997,'unittest','','');

INSERT INTO `buerger`
(`BuergerID`,`StandortID`,`Datum`,`Uhrzeit`,`Name`,`Anmerkung`,`Telefonnummer`,`EMail`,
 `AnzahlAufrufe`,`Timestamp`,`IPAdresse`,`IPTimeStamp`,`aufrufzeit`,`telefonnummer_fuer_rueckfragen`,
 `absagecode`,`AnzahlPersonen`,`vorlaeufigeBuchung`,`bestaetigt`,`status`,`displayNumber`)
VALUES
    (990029,65991,'2016-04-18','11:30:00','History Testperson','History Test amendment',
     '030 11111111','history@example.test',1,'11:35:00','127.0.0.1',1456312139,'11:35:00',
     '030 22222222','history-test-auth',1,0,1,'confirmed','H90029');

INSERT INTO `buergeranliegen`
(`BuergerID`,`BuergerarchivID`,`AnliegenID`,`source`)
VALUES
    (990029,0,9999997,'unittest');

INSERT INTO `process_search_history`
(`history_key`,`process_id`,`scope_id`,`display_number`,`appointment_at`,`booked_at`,
 `called_at`,`finalized_at`,`status`,`citizen_name`,`telephone`,`citizen_email`,
 `amendment`,`location_name`,`provider_name`,`services`)
VALUES
    (SHA2('retention-old',256),990201,65991,'R90201','2010-03-31 12:00:00','2010-03-01 10:00:00',
     NULL,'2010-03-31 13:00:00','completed','Retention Old','','',NULL,'HST','History Test Provider',NULL),

    (SHA2('retention-boundary',256),990202,65991,'R90202','2010-04-01 12:00:00','2010-03-01 10:00:00',
     NULL,'2010-04-01 13:00:00','completed','Retention Boundary','','',NULL,'HST','History Test Provider',NULL),

    (SHA2('retention-new',256),990203,65991,'R90203','2010-04-02 12:00:00','2010-03-01 10:00:00',
     NULL,'2010-04-02 13:00:00','completed','Retention New','','',NULL,'HST','History Test Provider',NULL);

UNLOCK TABLES;

/* ------------------------------------------------------------------
   Test-Daten AppointmentDeleteByCron History
-------------------------------------------------------------------*/

LOCK TABLES `buerger` WRITE, `buergeranliegen` WRITE;

DELETE FROM `buergeranliegen`
WHERE `BuergerID` IN (990101,990102,990103,990104,990105,990106,990107,990108,990109,990110);

DELETE FROM `buerger`
WHERE `BuergerID` IN (990101,990102,990103,990104,990105,990106,990107,990108,990109,990110);

INSERT INTO `buerger`
(`BuergerID`,`StandortID`,`Datum`,`Uhrzeit`,`Name`,`EMail`,`IPTimeStamp`,`Telefonnummer`,
 `telefonnummer_fuer_rueckfragen`,`absagecode`,`bestaetigt`,`vorlaeufigeBuchung`,`status`,
 `aufrufzeit`,`NutzerID`,`AbholortID`,`Abholer`,`nicht_erschienen`,`parked`,`aufruferfolgreich`,`Anmerkung`)
VALUES
    -- → missed
    (990101,65991,'2015-01-01','09:00:00','Cleanup Confirmed','cleanup-confirmed@example.test',
     1419984000,'030 100001','','cleanup-confirmed-auth',1,0,'confirmed','00:00:00',0,0,0,0,0,0,'Cleanup confirmed'),

    -- → missed
    (990102,65991,'2015-01-01','00:00:00','Cleanup Queued','cleanup-queued@example.test',
     1419984000,'030 100002','','cleanup-queued-auth',1,0,'queued','00:00:00',0,0,0,0,0,0,'Cleanup queued'),

    -- → missed
    (990103,65991,'2015-01-01','09:00:00','Cleanup Called','cleanup-called@example.test',
     1419984000,'030 100003','','cleanup-called-auth',1,0,'called','09:05:00',1,0,0,0,0,0,'Cleanup called'),

    -- → missed
    (990104,65991,'2015-01-01','09:00:00','Cleanup Missed','cleanup-missed@example.test',
     1419984000,'030 100004','','cleanup-missed-auth',1,0,'missed','00:00:00',0,0,0,1,0,0,'Cleanup missed'),

    -- → completed
    (990105,65991,'2015-01-01','09:00:00','Cleanup Processing','cleanup-processing@example.test',
     1419984000,'030 100005','','cleanup-processing-auth',1,0,'processing','00:00:00',1,0,0,0,0,1,'Cleanup processing'),

    -- → completed
    (990106,65991,'2015-01-01','09:00:00','Cleanup Parked','cleanup-parked@example.test',
     1419984000,'030 100006','','cleanup-parked-auth',1,0,'parked','00:00:00',0,0,0,0,1,0,'Cleanup parked'),

    -- → completed, aber nur bei --pending
    (990107,65991,'2015-01-01','09:00:00','Cleanup Pending','cleanup-pending@example.test',
     1419984000,'030 100007','','cleanup-pending-auth',1,0,'pending','00:00:00',0,65991,1,0,0,0,'Cleanup pending'),

    -- keine History
    (990108,65991,'2015-01-01','09:00:00','Cleanup Reserved','cleanup-reserved@example.test',
     1419984000,'030 100008','','cleanup-reserved-auth',0,1,'reserved','00:00:00',0,0,0,0,0,0,'Cleanup reserved'),

    -- keine History
    (990109,65991,'2015-01-01','09:00:00','Cleanup Deleted','cleanup-deleted@example.test',
     1419984000,'030 100009','','cleanup-deleted-auth',1,0,'deleted','00:00:00',0,0,0,0,0,0,'Cleanup deleted'),

    -- keine History
    (990110,65991,'2015-01-01','09:00:00','Cleanup Blocked','cleanup-blocked@example.test',
     1419984000,'030 100010','','cleanup-blocked-auth',1,0,'blocked','00:00:00',0,0,0,0,0,0,'Cleanup blocked');

INSERT INTO `buergeranliegen`
(`AnliegenID`,`source`,`BuergerID`)
VALUES
    (9999997,'unittest',990101),
    (9999997,'unittest',990102),
    (9999997,'unittest',990103),
    (9999997,'unittest',990104),
    (9999997,'unittest',990105),
    (9999997,'unittest',990106),
    (9999997,'unittest',990107),
    (9999997,'unittest',990108),
    (9999997,'unittest',990109),
    (9999997,'unittest',990110);

UNLOCK TABLES;