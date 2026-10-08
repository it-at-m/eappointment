-- ZMSKVR-1051 / ZMSKVR-1309 (+ ZMSKVR-1087 / ZMSKVR-1311, ZMSKVR-1530 / ZMSKVR-1541, ZMSKVR-843 / ZMSKVR-1014)
-- Distinct Hinweismeldungen bei verfübaren Terminen for Ruppertstraße Hauptkalender
-- scopes so ATAF can tell WB03 (181) from WB04 (160). One internet seat each so a
-- second booking of the same Wohnsitzanmeldung time switches Wartebereich.
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

-- V19 openings 136201 (160 / WB04) and 136202 (181 / WB03): one seat so the second
-- identical Wohnsitzanmeldung booking lands on the other Wartebereich.
UPDATE `oeffnungszeit`
SET `Anzahlterminarbeitsplaetze` = 1,
    `kommentar` = 'ZMSKVR-1051 ZMSKVR-1309 single internet seat for WB switch'
WHERE `OeffnungszeitID` IN (136201, 136202);
