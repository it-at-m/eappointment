-- ZMSKVR-1710: Briefbüro service 1064278 goes from 15 to 20 minutes.
-- Scope 46, provider 10412449 (KfZ Zulassungsstelle Briefbüro).
-- The scope has this one service. It occupies one slot before and after the
-- change, so booked appointments keep their start time and their slot count.
-- Opening hours at the scope are set to 20 minutes and their version is
-- raised so the next calculateSlots rebuilds the grid and links the existing
-- appointments to it.
-- Run this after the DLDB export already says 20 minutes. An hourly import
-- that still sees 15 minutes writes 15 back onto the opening hours.

START TRANSACTION;

UPDATE `oeffnungszeit` AS `availability`
INNER JOIN `standort` AS `scope`
    ON `scope`.`StandortID` = `availability`.`scope_id`
SET `availability`.`time_slot` = '00:20:00',
    `availability`.`version` = `availability`.`version` + 1
WHERE `scope`.`StandortID` = 46
  AND `scope`.`InfoDienstleisterID` = 10412449
  AND `scope`.`source` = 'dldb'
  AND TIME_TO_SEC(`availability`.`time_slot`) IN (900, 1200);

COMMIT;
