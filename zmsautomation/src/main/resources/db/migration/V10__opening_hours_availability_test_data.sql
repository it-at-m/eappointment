-- Flyway migration: Opening hours availability test data
-- Insert opening hours availability test data
-- Locations needed in ui tests:
-- 1 Gewerbeamt (KVR-III/21) Meldungen
-- 2 Gewerbeamt (KVR-III/23) Verkehr
-- 160 181 Bürgerbüro Ruppertstraße (KVR-II/22)
-- 172 184 Bürgerbüro Ruppertstraße (KVR-II/221)
-- 175 Bürgerbüro Ruppertstraße (KVR-II/225) Serviceschalter
-- 127 Bürgerbüro Orleansplatz (KVR-II/231 KP)
-- 169 Bürgerbüro Forstenrieder Allee (KVR-II/234)
-- 96 Standesamt München (KVR-II/112) Geburtenbüro
-- 253 Erstaufnahmeeinrichtung S‑III‑U

-- Dynamic opening-hours for UI tests
-- Mon–Fri only, skip feiertage, whole day 00:00–23:59, 10‑min slots
-- Rows carry Kommentar = 'ATAF dynamic opening-hours' for idempotency.

-- Flyway migration: Opening hours availability test data

-- Round current time up to the next 5-minute slot
SET @rounded_start :=
    SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / 300) * 300);

-- End time = start + 3 hours
SET @rounded_end :=
    ADDTIME(@rounded_start, '03:00:00');

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
(136180, 1,   CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136181, 2,   CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136182, 160, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136183, 181, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136184, 172, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136185, 184, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136186, 175, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136187, 127, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136188, 169, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136189, 93,  CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136190, 253, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 1, 0, 127, '00:00:00', @rounded_start, '00:00:00', @rounded_end, '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW());