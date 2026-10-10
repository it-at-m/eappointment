-- Flyway migration: Set email confirmation activated to false for scope 1 in ZMS-3171.feature Vorbelegung von "Mit E-Mail Bestätigung" ist konfigurierbar
-- Keep preferences in sync (idempotent upsert).
INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`)
VALUES ('scope', 1, 'client', 'nConfirmationActivated', '0', NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updateTimestamp` = VALUES(`updateTimestamp`);

UPDATE `standort` SET `email_confirmation_activated` = 0 WHERE `StandortID` = 1;

