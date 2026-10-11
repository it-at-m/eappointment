-- ZMSKVR-812 / ZMSKVR-830: distinct infoForAllAppointments on Feuerwachen so the
-- "Wann gibt es neue Termine?" modal can assert Standort grouping.
--
--   Föhring 274 / 10577: unique Föhring text (single-Ort + group heading)
--   Pasing 268 / 10585 + Neuperlach 277 / 10582: shared group text
--   Milbertshofen 271 / 10579: keep V47/V50 empty-state ATAF hint
--
-- Citizen API reads standort.info_for_all_appointments (V50); preferences stay in sync.

UPDATE `standort`
SET `info_for_all_appointments` = '<p>ATAF Hinweis Feuerwache Föhring: Termine dienstags.</p>'
WHERE `StandortID` = 274;

UPDATE `standort`
SET `info_for_all_appointments` = '<p>ATAF Hinweis Feuerwache Gruppe: Termine mittwochs.</p>'
WHERE `StandortID` IN (268, 277);

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`)
VALUES
  (
    'scope',
    274,
    'appointment',
    'infoForAllAppointments',
    '<p>ATAF Hinweis Feuerwache Föhring: Termine dienstags.</p>',
    NOW()
  ),
  (
    'scope',
    268,
    'appointment',
    'infoForAllAppointments',
    '<p>ATAF Hinweis Feuerwache Gruppe: Termine mittwochs.</p>',
    NOW()
  ),
  (
    'scope',
    277,
    'appointment',
    'infoForAllAppointments',
    '<p>ATAF Hinweis Feuerwache Gruppe: Termine mittwochs.</p>',
    NOW()
  )
ON DUPLICATE KEY UPDATE
  `value` = VALUES(`value`),
  `updateTimestamp` = VALUES(`updateTimestamp`);
