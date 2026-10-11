-- ZMSKVR-1083 / ZMSKVR-1226: Feuerwache peer slotsPerAppointment for combination qty.
--
-- Führungen (10389330) is the only public Feuerwache service (1 slot). Bürgerbüro
-- peers share Wohnsitz and cannot prove the spa/maxQuantity min on the counter.
-- Set Föhring (scope 274 / provider 10577) spa=8 and Milbertshofen (scope 271 /
-- provider 10579) spa=2 so the combination min spa is 2 (would allow qty 2).
-- Live SADB maxQuantity stays 1 → UI max is min(1, floor(2/1))=1.

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`)
VALUES
  ('scope', 274, 'client', 'slotsPerAppointment', '8', NOW()),
  ('scope', 271, 'client', 'slotsPerAppointment', '2', NOW())
ON DUPLICATE KEY UPDATE
  `value` = VALUES(`value`),
  `updateTimestamp` = VALUES(`updateTimestamp`);
