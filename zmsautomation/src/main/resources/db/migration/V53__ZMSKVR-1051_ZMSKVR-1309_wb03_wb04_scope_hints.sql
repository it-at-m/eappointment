-- ZMSKVR-1051 / ZMSKVR-1309 (+ ZMSKVR-1087 / ZMSKVR-1311, ZMSKVR-1530 / ZMSKVR-1541, ZMSKVR-843 / ZMSKVR-1014)
-- Distinct Hinweismeldungen bei verfübaren Terminen for Ruppertstraße Hauptkalender
-- scopes so ATAF can tell WB03 (181) from WB04 (160).
-- HTML (no wrapping <p>) exercises sanitizeHtml + the product p-wrap on overview/detail.
-- Citizen API / backend Scope reads standort.info_for_appointment (and preferences shadow).

UPDATE `standort`
SET `info_for_appointment` =
  'ATAF Hinweis WB04<br><a href="https://www.wikipedia.de/">ATAF Link WB04</a> <em>kursiv WB04</em> <strong>fett WB04</strong>'
WHERE `StandortID` = 160;

UPDATE `standort`
SET `info_for_appointment` =
  'ATAF Hinweis WB03<br><a href="https://www.wikipedia.de/">ATAF Link WB03</a> <em>kursiv WB03</em> <strong>fett WB03</strong>'
WHERE `StandortID` = 181;

UPDATE `preferences`
SET `value` =
  'ATAF Hinweis WB04<br><a href="https://www.wikipedia.de/">ATAF Link WB04</a> <em>kursiv WB04</em> <strong>fett WB04</strong>',
    `updateTimestamp` = NOW()
WHERE `entity` = 'scope'
  AND `id` = 160
  AND `groupName` = 'appointment'
  AND `name` = 'infoForAppointment';

UPDATE `preferences`
SET `value` =
  'ATAF Hinweis WB03<br><a href="https://www.wikipedia.de/">ATAF Link WB03</a> <em>kursiv WB03</em> <strong>fett WB03</strong>',
    `updateTimestamp` = NOW()
WHERE `entity` = 'scope'
  AND `id` = 181
  AND `groupName` = 'appointment'
  AND `name` = 'infoForAppointment';

-- Give the switch test a dedicated day after V22's shared openings. One seat on
-- each scope makes the second same-time booking switch to the other Wartebereich.
SET @switch_day := (
    SELECT DATE_ADD(MAX(`Endedatum`), INTERVAL 1 DAY)
    FROM `oeffnungszeit`
    WHERE `OeffnungszeitID` IN (136201, 136202)
);

INSERT IGNORE INTO `oeffnungszeit` (
    `OeffnungszeitID`, `StandortID`, `Startdatum`, `Endedatum`, `allexWochen`, `jedexteWoche`,
    `Wochentag`, `Anfangszeit`, `Terminanfangszeit`, `Endzeit`, `Terminendzeit`, `Timeslot`,
    `Anzahlarbeitsplaetze`, `Anzahlterminarbeitsplaetze`, `kommentar`, `reduktionTermineImInternet`,
    `erlaubemehrfachslots`, `Offen_ab`, `Offen_bis`, `updateTimestamp`
) VALUES
    (136511, 160, @switch_day, @switch_day, 1, 0, 127, '00:00:00', '08:00:00', '00:00:00', '18:00:00',
     '00:05:00', 0, 1, 'ZMSKVR-1051 ZMSKVR-1309 WB04 scope-switch day after shared range', 0, 5, 0, 30, NOW()),
    (136512, 181, @switch_day, @switch_day, 1, 0, 127, '00:00:00', '08:00:00', '00:00:00', '18:00:00',
     '00:05:00', 0, 1, 'ZMSKVR-1051 ZMSKVR-1309 WB03 scope-switch day after shared range', 0, 5, 0, 30, NOW());
