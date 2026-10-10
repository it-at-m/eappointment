-- ZMSKVR-1083 / ZMSKVR-1226: Feuerwache min slotsPerAppointment for combination qty.
--
-- Führungen (10389330) is the only public Feuerwache service (1 slot). Bürgerbüro
-- peers share Wohnsitz and cannot prove min-spa on the counter. Set Föhring
-- (scope 274 / provider 10577) spa=8 and Milbertshofen (scope 271 / provider
-- 10579) spa=2. With services_de.json maxQuantity=5, Föhring alone would allow
-- 5; min spa across Feuerwachen stops the plus at 2.
-- request_provider.max_quantity stays in sync for relation-side checks.

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`)
VALUES
  ('scope', 274, 'client', 'slotsPerAppointment', '8', NOW()),
  ('scope', 271, 'client', 'slotsPerAppointment', '2', NOW())
ON DUPLICATE KEY UPDATE
  `value` = VALUES(`value`),
  `updateTimestamp` = VALUES(`updateTimestamp`);

UPDATE `request_provider`
SET `max_quantity` = 5
WHERE `source` = 'dldb'
  AND `request__id` = '10389330';
