-- Flyway migration: Opening hours availability test data

-- round current time to next 5 minute slot
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / 300) * 300);

-- desired end = +3 hours
SET @desired_end :=
  ADDTIME(@rounded_start, '03:00:00');

-- latest allowed end so slots still fit
SET @rounded_end :=
  LEAST(@desired_end, '23:55:00');

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
(136180, 1,   CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136181, 2,   CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136182, 160, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136183, 181, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136184, 172, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136185, 184, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136186, 175, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136187, 127, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136188, 169, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136189, 93,  CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW()),
(136190, 253, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Test data Öffnungszeit', 0, 1, 0, 30, NOW());