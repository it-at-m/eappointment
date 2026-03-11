-- Flyway migration: Opening hours for Ruppertstraße test data (ZMSKVR-1124)
--
-- Mirrors V10__opening_hours_availability_test_data but focuses on
-- Ruppertstraße offices used in Citizen API booking.feature:
--  - Office 10489 (Bürgerbüro Ruppertstraße (KVR-II/22))
--  - Office 10492 (Bürgerbüro Ruppertstraße (KVR-II/211))
--  - Office 10502 (Bürgerbüro Ruppertstraße (KVR-II/221))
--
-- Standorte involved (see V5__standort_test_data):
--  - 148 -> officeId 10492 (Abholung, KVR-II/211)
--  - 160 -> officeId 10489 (WB04, KVR-II/22)
--  - 181 -> officeId 10489 (WB03, KVR-II/22)
--  - 172 -> officeId 10502 (WB04 Pass, KVR-II/221)
--  - 184 -> officeId 10502 (WB03 Pass, KVR-II/221)
--  - 342 -> officeId 10502 (Serviceschalter Pass, KVR-II/221)

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
  -- Bürgerbüro Ruppertstraße (KVR-II/211) – Abholung (officeId 10492)
  (136200, 148, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY),
   1, 0, 127,
   '00:00:00', @rounded_start,
   '00:00:00', @rounded_end,
   '00:05:00',
   0, 3,
   'ZMSKVR-1124 Ruppertstraße Öffnungszeit',
   0, 1,
   0, 30,
   NOW()),

  -- Bürgerbüro Ruppertstraße (KVR-II/22) – WB04 (officeId 10489)
  (136201, 160, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY),
   1, 0, 127,
   '00:00:00', @rounded_start,
   '00:00:00', @rounded_end,
   '00:05:00',
   0, 3,
   'ZMSKVR-1124 Ruppertstraße Öffnungszeit',
   0, 1,
   0, 30,
   NOW()),

  -- Bürgerbüro Ruppertstraße (KVR-II/22) – WB03 (officeId 10489)
  (136202, 181, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY),
   1, 0, 127,
   '00:00:00', @rounded_start,
   '00:00:00', @rounded_end,
   '00:05:00',
   0, 3,
   'ZMSKVR-1124 Ruppertstraße Öffnungszeit',
   0, 1,
   0, 30,
   NOW()),

  -- Bürgerbüro Ruppertstraße (KVR-II/221) – WB04 Pass (officeId 10502)
  (136203, 172, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY),
   1, 0, 127,
   '00:00:00', @rounded_start,
   '00:00:00', @rounded_end,
   '00:05:00',
   0, 3,
   'ZMSKVR-1124 Ruppertstraße Öffnungszeit',
   0, 1,
   0, 30,
   NOW()),

  -- Bürgerbüro Ruppertstraße (KVR-II/221) – WB03 Pass (officeId 10502)
  (136204, 184, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY),
   1, 0, 127,
   '00:00:00', @rounded_start,
   '00:00:00', @rounded_end,
   '00:05:00',
   0, 3,
   'ZMSKVR-1124 Ruppertstraße Öffnungszeit',
   0, 1,
   0, 30,
   NOW()),

  -- Bürgerbüro Ruppertstraße (KVR-II/221) – Serviceschalter Pass (officeId 10502)
  (136205, 342, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY),
   1, 0, 127,
   '00:00:00', @rounded_start,
   '00:00:00', @rounded_end,
   '00:05:00',
   0, 3,
   'ZMSKVR-1124 Ruppertstraße Öffnungszeit',
   0, 1,
   0, 30,
   NOW());

