-- ZMSKVR-833 / ZMSKVR-1025: rebooking from Ausbildung (optional remarks) onto Haupt (required remarks).
--
-- V24 cloned Pass WB04 onto Ausbildung 372 and left standort.custom_text_field_required=1.
-- First booking then fills Bemerkung as Pflichtfeld; rebooking onto Haupt 160 skips Kontakt.
-- V6 has customTextfieldActivated for 160 but no customTextfieldRequired row.
-- Keep standort columns and preferences in sync (same pattern as V17).

UPDATE `standort`
SET `custom_text_field_required` = 0
WHERE `StandortID` = 372;

UPDATE `standort`
SET `custom_text_field_required` = 1
WHERE `StandortID` = 160;

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`)
VALUES ('scope', 372, 'client', 'customTextfieldRequired', '0', NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updateTimestamp` = VALUES(`updateTimestamp`);

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`, `updateTimestamp`)
VALUES ('scope', 160, 'client', 'customTextfieldRequired', '1', NOW())
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updateTimestamp` = VALUES(`updateTimestamp`);
