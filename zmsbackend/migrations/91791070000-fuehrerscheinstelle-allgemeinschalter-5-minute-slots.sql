-- Führerscheinstelle Allgemeinschalter A, B and C go from 20-minute slots to 5.
-- Scopes 6, 256 and 347, provider 10308174, source dldb.
-- 5 divides 20, so every booked start already sits on the new grid. The start
-- time stays. One old slot occupied 20 minutes, which is four 5-minute slots,
-- so an upcoming appointment gains the follow-up rows for the minutes in
-- between and hatFolgetermine grows to match. Past and cancelled appointments
-- stay as they are.
-- The hourly import leaves existing opening hours unchanged. This sets them
-- to 5 minutes, allows multiple slots (15- and 20-minute services need 3 and
-- 4 slots), and raises their version so the next calculateSlots rebuilds the
-- grid and links the existing appointments to it.
-- A positive slotsPerAppointment below 4 is raised to 4. NULL, 0 and values
-- that are already high enough stay as they are.
-- Scope 350 is not touched: its appointments are worked off before that
-- counter is reconfigured.
-- NOW() follows zms-db TZ=Europe/Berlin.
-- Service slot counts and the ten new services come from the DLDB export.
-- Run this while these opening hours are still 20 minutes. A second run finds
-- no 20-minute rows and does nothing.

START TRANSACTION;

DROP TEMPORARY TABLE IF EXISTS `tmp_zmskvr_fs_scopes`;
CREATE TEMPORARY TABLE `tmp_zmskvr_fs_scopes` (
    `scope_id` INT UNSIGNED NOT NULL PRIMARY KEY
);

INSERT INTO `tmp_zmskvr_fs_scopes` (`scope_id`)
SELECT DISTINCT `scope`.`StandortID`
FROM `standort` AS `scope`
INNER JOIN `oeffnungszeit` AS `availability`
    ON `availability`.`scope_id` = `scope`.`StandortID`
WHERE `scope`.`StandortID` IN (6, 256, 347)
  AND `scope`.`InfoDienstleisterID` = 10308174
  AND `scope`.`source` = 'dldb'
  AND TIME_TO_SEC(`availability`.`time_slot`) = 1200;

DROP TEMPORARY TABLE IF EXISTS `tmp_zmskvr_fs_needed`;
CREATE TEMPORARY TABLE `tmp_zmskvr_fs_needed` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `parent_id` INT UNSIGNED NOT NULL,
    `scope_id` INT UNSIGNED NOT NULL,
    `day` DATE NOT NULL,
    `slot_time` TIME NOT NULL,
    `status` VARCHAR(20) NULL,
    `provisional` INT NOT NULL,
    `confirmed` TINYINT NOT NULL,
    `ip_timestamp` BIGINT NOT NULL,
    `api_client_id` INT UNSIGNED NULL,
    KEY `idx_parent_time` (`parent_id`, `slot_time`)
);

INSERT INTO `tmp_zmskvr_fs_needed` (
    `parent_id`,
    `scope_id`,
    `day`,
    `slot_time`,
    `status`,
    `provisional`,
    `confirmed`,
    `ip_timestamp`,
    `api_client_id`
)
SELECT
    `process`.`BuergerID`,
    `process`.`StandortID`,
    `process`.`Datum`,
    ADDTIME(`process`.`Uhrzeit`, SEC_TO_TIME(`offset`.`n` * 300)),
    `process`.`status`,
    `process`.`vorlaeufigeBuchung`,
    `process`.`bestaetigt`,
    `process`.`IPTimeStamp`,
    `process`.`apiClientID`
FROM `buerger` AS `process`
INNER JOIN `tmp_zmskvr_fs_scopes` AS `chosen`
    ON `chosen`.`scope_id` = `process`.`StandortID`
INNER JOIN (
    SELECT 1 AS `n` UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
    UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8
    UNION ALL SELECT 9 UNION ALL SELECT 10 UNION ALL SELECT 11 UNION ALL SELECT 12
    UNION ALL SELECT 13 UNION ALL SELECT 14 UNION ALL SELECT 15 UNION ALL SELECT 16
    UNION ALL SELECT 17 UNION ALL SELECT 18 UNION ALL SELECT 19 UNION ALL SELECT 20
    UNION ALL SELECT 21 UNION ALL SELECT 22 UNION ALL SELECT 23 UNION ALL SELECT 24
) AS `offset`
    ON `offset`.`n` < LEAST((IFNULL(`process`.`hatFolgetermine`, 0) + 1) * 4, 25)
   AND TIME_TO_SEC(`process`.`Uhrzeit`) + `offset`.`n` * 300 < 86400
WHERE (`process`.`istFolgeterminvon` IS NULL OR `process`.`istFolgeterminvon` = 0)
  AND `process`.`Name` <> '(abgesagt)'
  AND `process`.`Name` <> 'dereferenced'
  AND `process`.`Name` <> '(Folgetermin)'
  AND `process`.`Uhrzeit` > '00:00:00'
  AND TIMESTAMP(`process`.`Datum`, `process`.`Uhrzeit`)
        + INTERVAL (IFNULL(`process`.`hatFolgetermine`, 0) + 1) * 20 MINUTE > NOW()
  AND NOT EXISTS (
      SELECT 1
      FROM `buerger` AS `followup`
      WHERE `followup`.`istFolgeterminvon` = `process`.`BuergerID`
        AND `followup`.`StandortID` = `process`.`StandortID`
        AND `followup`.`Datum` = `process`.`Datum`
        AND `followup`.`Uhrzeit` = ADDTIME(`process`.`Uhrzeit`, SEC_TO_TIME(`offset`.`n` * 300))
  )
FOR UPDATE;

DROP TEMPORARY TABLE IF EXISTS `tmp_zmskvr_fs_ids`;
CREATE TEMPORARY TABLE `tmp_zmskvr_fs_ids` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `process_id` INT UNSIGNED NOT NULL
);

INSERT INTO `tmp_zmskvr_fs_ids` (`process_id`)
SELECT `sequence`.`processId`
FROM `process_sequence` AS `sequence`
LEFT JOIN `buerger` AS `existing`
    ON `existing`.`BuergerID` = `sequence`.`processId`
WHERE `existing`.`BuergerID` IS NULL
  AND `sequence`.`processId` >= 100000
ORDER BY `sequence`.`processId`
LIMIT 200000
FOR UPDATE;

INSERT INTO `buerger` (
    `BuergerID`,
    `StandortID`,
    `Datum`,
    `Uhrzeit`,
    `Name`,
    `status`,
    `istFolgeterminvon`,
    `hatFolgetermine`,
    `absagecode`,
    `vorlaeufigeBuchung`,
    `bestaetigt`,
    `IPTimeStamp`,
    `apiClientID`,
    `displayNumber`
)
SELECT
    `ids`.`process_id`,
    `needed`.`scope_id`,
    `needed`.`day`,
    `needed`.`slot_time`,
    '(Folgetermin)',
    `needed`.`status`,
    `needed`.`parent_id`,
    0,
    LOWER(HEX(RANDOM_BYTES(32))),
    `needed`.`provisional`,
    `needed`.`confirmed`,
    `needed`.`ip_timestamp`,
    IFNULL(`needed`.`api_client_id`, 1),
    ''
FROM `tmp_zmskvr_fs_needed` AS `needed`
INNER JOIN `tmp_zmskvr_fs_ids` AS `ids`
    ON `ids`.`id` = `needed`.`id`;

SELECT COUNT(*) INTO @needed_slots FROM `tmp_zmskvr_fs_needed`;

SELECT COUNT(*) INTO @created_slots
FROM `tmp_zmskvr_fs_needed` AS `needed`
INNER JOIN `tmp_zmskvr_fs_ids` AS `ids`
    ON `ids`.`id` = `needed`.`id`
INNER JOIN `buerger` AS `created`
    ON `created`.`BuergerID` = `ids`.`process_id`
   AND `created`.`istFolgeterminvon` = `needed`.`parent_id`
   AND `created`.`Uhrzeit` = `needed`.`slot_time`;

-- Duplicate primary key aborts the statement, and the open transaction rolls
-- back, when a follow-up could not be stored.
INSERT INTO `process_sequence` (`processId`)
SELECT `seed`.`processId`
FROM (
    SELECT MIN(`processId`) AS `processId`
    FROM `process_sequence`
) AS `seed`
WHERE @needed_slots <> @created_slots;

UPDATE `buerger` AS `process`
INNER JOIN `tmp_zmskvr_fs_scopes` AS `chosen`
    ON `chosen`.`scope_id` = `process`.`StandortID`
SET `process`.`hatFolgetermine` = LEAST((IFNULL(`process`.`hatFolgetermine`, 0) + 1) * 4, 25) - 1
WHERE (`process`.`istFolgeterminvon` IS NULL OR `process`.`istFolgeterminvon` = 0)
  AND `process`.`Name` <> '(abgesagt)'
  AND `process`.`Name` <> 'dereferenced'
  AND `process`.`Name` <> '(Folgetermin)'
  AND `process`.`Uhrzeit` > '00:00:00'
  AND TIMESTAMP(`process`.`Datum`, `process`.`Uhrzeit`)
        + INTERVAL (IFNULL(`process`.`hatFolgetermine`, 0) + 1) * 20 MINUTE > NOW();

UPDATE `oeffnungszeit` AS `availability`
INNER JOIN `tmp_zmskvr_fs_scopes` AS `chosen`
    ON `chosen`.`scope_id` = `availability`.`scope_id`
SET `availability`.`time_slot` = '00:05:00',
    `availability`.`multiple_slots_allowed` = 1,
    `availability`.`version` = `availability`.`version` + 1
WHERE TIME_TO_SEC(`availability`.`time_slot`) = 1200;

UPDATE `standort` AS `scope`
INNER JOIN `tmp_zmskvr_fs_scopes` AS `chosen`
    ON `chosen`.`scope_id` = `scope`.`StandortID`
SET `scope`.`slots_per_appointment` = 4
WHERE `scope`.`slots_per_appointment` IS NOT NULL
  AND `scope`.`slots_per_appointment` > 0
  AND `scope`.`slots_per_appointment` < 4;

UPDATE `preferences` AS `preference`
INNER JOIN `tmp_zmskvr_fs_scopes` AS `chosen`
    ON `chosen`.`scope_id` = `preference`.`id`
SET `preference`.`value` = '4',
    `preference`.`updateTimestamp` = NOW()
WHERE `preference`.`entity` = 'scope'
  AND `preference`.`groupName` = 'client'
  AND `preference`.`name` = 'slotsPerAppointment'
  AND `preference`.`value` REGEXP '^[0-9]+$'
  AND CAST(`preference`.`value` AS UNSIGNED) > 0
  AND CAST(`preference`.`value` AS UNSIGNED) < 4;

DROP TEMPORARY TABLE IF EXISTS `tmp_zmskvr_fs_scopes`;
DROP TEMPORARY TABLE IF EXISTS `tmp_zmskvr_fs_needed`;
DROP TEMPORARY TABLE IF EXISTS `tmp_zmskvr_fs_ids`;

COMMIT;
