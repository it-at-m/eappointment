-- ZMSKVR-1613 / ZMSKVR-1621: the overall view must not draw walk-in hours in white.
--
-- Standesamt (KVR-II/1131), Standort 90, has no other opening hours.
-- Walk-in customers are open 08:00–18:00. Appointments are only 09:00–17:00.
-- The hour before 09:00 is the gap the overall view must leave out.
-- CURDATE() follows zms-db TZ=Europe/Berlin.

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
  136480,
  90,
  CURDATE(),
  DATE_ADD(CURDATE(), INTERVAL 14 DAY),
  1,
  0,
  127,
  '08:00:00',
  '09:00:00',
  '18:00:00',
  '17:00:00',
  '00:05:00',
  0,
  1,
  'ZMSKVR-1613 ZMSKVR-1621 walk-in gap',
  0,
  1,
  0,
  30,
  NOW()
);
