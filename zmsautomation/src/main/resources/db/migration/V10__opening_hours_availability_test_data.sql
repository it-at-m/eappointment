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



INSERT IGNORE INTO `oeffnungszeit` (`OeffnungszeitID`, `StandortID`, `Startdatum`, `Endedatum`, `allexWochen`, `jedexteWoche`, `Wochentag`, `Anfangszeit`, `Terminanfangszeit`, `Endzeit`, `Terminendzeit`, `Timeslot`, `Anzahlarbeitsplaetze`, `Anzahlterminarbeitsplaetze`, `kommentar`, `reduktionTermineImInternet`, `erlaubemehrfachslots`, `Offen_ab`, `Offen_bis`, `updateTimestamp`) VALUES
(136180, 1, '2026-02-24', '2026-03-02', 1, 0, 127, '00:00:00', '06:00:00', '00:00:00', '22:00:00', '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136181, 2, '2026-02-24', '2026-03-02', 1, 0, 127, '00:00:00', '06:00:00', '00:00:00', '22:00:00', '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136182, 160, '2026-02-24', '2026-03-02', 1, 0, 127, '00:00:00', '06:00:00', '00:00:00', '22:00:00', '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136183, 181, '2026-02-24', '2026-03-02', 1, 0, 127, '00:00:00', '06:00:00', '00:00:00', '22:00:00', '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136184, 172, '2026-02-24', '2026-03-02', 1, 0, 127, '00:00:00', '06:00:00', '00:00:00', '22:00:00', '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136185, 184, '2026-02-24', '2026-03-02', 1, 0, 127, '00:00:00', '06:00:00', '00:00:00', '22:00:00', '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136183, 175, '2026-02-24', '2026-03-02', 1, 0, 127, '00:00:00', '06:00:00', '00:00:00', '22:00:00', '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136187, 127, '2026-02-24', '2026-03-02', 1, 0, 127, '00:00:00', '06:00:00', '00:00:00', '22:00:00', '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136188, 169, '2026-02-24', '2026-03-02', 1, 0, 127, '00:00:00', '06:00:00', '00:00:00', '22:00:00', '00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136189, 93, '2026-02-24', '2026-03-02', 1, 0, 127, '00:00:00','06:00:00','00:00:00','22:00:00','00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW()),
(136190, 253, '2026-02-24', '2026-03-02', 1, 0, 127,'00:00:00','06:00:00','00:00:00','22:00:00','00:05:00', 0, 3, 'Neue Öffnungszeit', 0, 1, 0, 30, NOW());