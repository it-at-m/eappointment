-- ZMSKVR-1620 / ZMSKVR-1691: disable citizen rescheduling for one bookable scope.
--
-- Planeinsicht Kleinkunde (Standort 378, officeId 5, service 6) already has opening
-- hours from V36 and is not used by positive Umbuchung features (those use 10492 /
-- Rente). Dual-write standort.rebooking_disabled and the preferences shadow row —
-- Citizen API maps both via Scope::isRebookingDisabled().

UPDATE `standort`
SET `rebooking_disabled` = 1
WHERE `StandortID` = 378;

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`)
VALUES ('scope', 378, 'appointment', 'rebookingDisabled', '1', NOW())
ON DUPLICATE KEY UPDATE
  `value` = VALUES(`value`),
  `updateTimestamp` = VALUES(`updateTimestamp`);
