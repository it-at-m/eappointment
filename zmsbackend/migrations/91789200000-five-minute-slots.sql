-- One pass onto a 5-minute slot. Opening hours, service slot counts, the
-- appointment cap, and provider slot length move together. A booked appointment
-- keeps its process number. Each extra 5-minute step gets a Folgetermin row
-- with its own process number. hatFolgetermine on the parent is the number of
-- those steps. The slot grid is not rebuilt here. calculateSlots does that,
-- one location at a time, after this migration.

DROP TEMPORARY TABLE IF EXISTS `tmp_scope_minutes`;
DROP TEMPORARY TABLE IF EXISTS `tmp_parents`;
DROP TEMPORARY TABLE IF EXISTS `tmp_step`;
DROP TEMPORARY TABLE IF EXISTS `tmp_followups`;
DROP TEMPORARY TABLE IF EXISTS `tmp_free_ids`;
DROP PROCEDURE IF EXISTS `zms_five_minute_require_process_ids`;

CREATE TEMPORARY TABLE `tmp_scope_minutes` (
    `scope_id` INT UNSIGNED NOT NULL PRIMARY KEY,
    `minutes` INT NOT NULL,
    `kinds` INT NOT NULL
);

INSERT INTO `tmp_scope_minutes` (`scope_id`, `minutes`, `kinds`)
SELECT `scope_id`,
       MIN(FLOOR(TIME_TO_SEC(`time_slot`) / 60)),
       COUNT(DISTINCT FLOOR(TIME_TO_SEC(`time_slot`) / 60))
FROM `oeffnungszeit`
WHERE TIME_TO_SEC(`time_slot`) > 0
GROUP BY `scope_id`;

CREATE TEMPORARY TABLE `tmp_parents` (
    `parent_id` INT UNSIGNED NOT NULL PRIMARY KEY,
    `steps` INT NOT NULL
);

INSERT INTO `tmp_parents` (`parent_id`, `steps`)
SELECT `src`.`BuergerID`,
       ((IFNULL(`src`.`hatFolgetermine`, 0) + 1) * `src`.`minutes`) DIV 5
FROM (
    SELECT `p`.`BuergerID`,
           `p`.`hatFolgetermine`,
           CASE
               WHEN `m`.`kinds` = 1 THEN `m`.`minutes`
               ELSE (
                   SELECT `sl`.`slotTimeInMinutes`
                   FROM `slot` `sl`
                   WHERE `sl`.`scopeID` = `p`.`StandortID`
                     AND `sl`.`year` = YEAR(`p`.`Datum`)
                     AND `sl`.`month` = MONTH(`p`.`Datum`)
                     AND `sl`.`day` = DAY(`p`.`Datum`)
                     AND `sl`.`status` IN ('free', 'full')
                     AND `p`.`Uhrzeit` BETWEEN `sl`.`time`
                       AND SEC_TO_TIME(TIME_TO_SEC(`sl`.`time`) + (`sl`.`slotTimeInMinutes` * 60) - 1)
                   ORDER BY `sl`.`slotTimeInMinutes` DESC
                   LIMIT 1
               )
           END AS `minutes`,
           `p`.`Uhrzeit`
    FROM `buerger` `p`
    LEFT JOIN `tmp_scope_minutes` `m` ON `m`.`scope_id` = `p`.`StandortID`
    WHERE (`p`.`istFolgeterminvon` IS NULL OR `p`.`istFolgeterminvon` = 0)
      AND `p`.`Uhrzeit` <> '00:00:00'
      AND (`p`.`status` IS NULL OR `p`.`status` <> 'deleted')
) `src`
WHERE `src`.`minutes` > 0
  AND MOD(`src`.`minutes`, 5) = 0
  AND ((IFNULL(`src`.`hatFolgetermine`, 0) + 1) * `src`.`minutes`) DIV 5 >= 1
  AND TIME_TO_SEC(`src`.`Uhrzeit`)
      + ((((IFNULL(`src`.`hatFolgetermine`, 0) + 1) * `src`.`minutes`) DIV 5 - 1) * 300) < 86400;

CREATE TEMPORARY TABLE `tmp_step` (
    `n` INT NOT NULL PRIMARY KEY
);

INSERT INTO `tmp_step` (`n`)
WITH RECURSIVE `seq` AS (
    SELECT 1 AS `n`
    UNION ALL
    SELECT `n` + 1 FROM `seq` WHERE `n` < 287
)
SELECT `n` FROM `seq`;

CREATE TEMPORARY TABLE `tmp_followups` (
    `seq` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `parent_id` INT UNSIGNED NOT NULL,
    `uhrzeit` TIME NOT NULL
);

INSERT INTO `tmp_followups` (`parent_id`, `uhrzeit`)
SELECT `t`.`parent_id`,
       SEC_TO_TIME(TIME_TO_SEC(`p`.`Uhrzeit`) + (`nums`.`n` * 300))
FROM `tmp_parents` `t`
INNER JOIN `buerger` `p` ON `p`.`BuergerID` = `t`.`parent_id`
INNER JOIN `tmp_step` `nums` ON `nums`.`n` < `t`.`steps`
LEFT JOIN `buerger` `child`
    ON `child`.`istFolgeterminvon` = `t`.`parent_id`
   AND `child`.`Datum` = `p`.`Datum`
   AND `child`.`StandortID` = `p`.`StandortID`
   AND `child`.`Uhrzeit` = SEC_TO_TIME(TIME_TO_SEC(`p`.`Uhrzeit`) + (`nums`.`n` * 300))
   AND (`child`.`status` IS NULL OR `child`.`status` <> 'deleted')
WHERE `t`.`steps` > 1
  AND `child`.`BuergerID` IS NULL
ORDER BY `t`.`parent_id`, `nums`.`n`;

CREATE TEMPORARY TABLE `tmp_free_ids` (
    `seq` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `process_id` INT UNSIGNED NOT NULL
);

SET @needed = (SELECT COUNT(*) FROM `tmp_followups`);

SET @free_id_sql = CONCAT(
    'INSERT INTO `tmp_free_ids` (`process_id`) ',
    'SELECT `ps`.`processId` FROM `process_sequence` `ps` ',
    'LEFT JOIN `buerger` `b` ON `b`.`BuergerID` = `ps`.`processId` ',
    'WHERE `b`.`BuergerID` IS NULL ',
    'ORDER BY `ps`.`processId` LIMIT ',
    @needed
);

PREPARE `zms_followup_ids` FROM @free_id_sql;
EXECUTE `zms_followup_ids`;
DEALLOCATE PREPARE `zms_followup_ids`;

DELIMITER $$

CREATE PROCEDURE `zms_five_minute_require_process_ids`(IN `needed` INT, IN `available` INT)
BEGIN
    IF `available` < `needed` THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Not enough free process ids for Folgetermine';
    END IF;
END$$

DELIMITER ;

SET @available = (SELECT COUNT(*) FROM `tmp_free_ids`);
CALL `zms_five_minute_require_process_ids`(@needed, @available);
DROP PROCEDURE `zms_five_minute_require_process_ids`;

START TRANSACTION;

UPDATE `buerger` `b`
INNER JOIN `tmp_parents` `t` ON `t`.`parent_id` = `b`.`BuergerID`
SET `b`.`hatFolgetermine` = `t`.`steps` - 1
WHERE `b`.`hatFolgetermine` IS NULL OR `b`.`hatFolgetermine` <> `t`.`steps` - 1;

INSERT INTO `buerger` (
    `BuergerID`,
    `StandortID`,
    `Datum`,
    `Uhrzeit`,
    `Name`,
    `IPAdresse`,
    `IPTimeStamp`,
    `hatFolgetermine`,
    `istFolgeterminvon`,
    `absagecode`,
    `vorlaeufigeBuchung`,
    `bestaetigt`,
    `apiClientID`,
    `status`
)
SELECT `ids`.`process_id`,
       `p`.`StandortID`,
       `p`.`Datum`,
       `f`.`uhrzeit`,
       '(Folgetermin)',
       `p`.`IPAdresse`,
       UNIX_TIMESTAMP(),
       0,
       `f`.`parent_id`,
       SHA2(CONCAT(IFNULL(`p`.`absagecode`, ''), ':', `ids`.`process_id`, ':', UUID()), 256),
       `p`.`vorlaeufigeBuchung`,
       `p`.`bestaetigt`,
       `p`.`apiClientID`,
       `p`.`status`
FROM `tmp_followups` `f`
INNER JOIN `tmp_free_ids` `ids` ON `ids`.`seq` = `f`.`seq`
INNER JOIN `buerger` `p` ON `p`.`BuergerID` = `f`.`parent_id`;

UPDATE `preferences` `pref`
INNER JOIN `standort` `s` ON `s`.`StandortID` = `pref`.`id`
INNER JOIN `provider` `p` ON `p`.`id` = `s`.`InfoDienstleisterID` AND `p`.`source` = `s`.`source`
SET `pref`.`value` = CAST(
    CAST(`pref`.`value` AS UNSIGNED) * (
        CAST(JSON_UNQUOTE(JSON_EXTRACT(`p`.`data`, '$.slotTimeInMinutes')) AS UNSIGNED) DIV 5
    ) AS CHAR
)
WHERE `pref`.`entity` = 'scope'
  AND `pref`.`groupName` = 'client'
  AND `pref`.`name` = 'slotsPerAppointment'
  AND `pref`.`value` REGEXP '^[0-9]+$'
  AND CAST(`pref`.`value` AS UNSIGNED) > 0
  AND JSON_VALID(`p`.`data`)
  AND CAST(JSON_UNQUOTE(JSON_EXTRACT(`p`.`data`, '$.slotTimeInMinutes')) AS UNSIGNED) > 5
  AND MOD(CAST(JSON_UNQUOTE(JSON_EXTRACT(`p`.`data`, '$.slotTimeInMinutes')) AS UNSIGNED), 5) = 0;

UPDATE `standort` `s`
INNER JOIN `provider` `p` ON `p`.`id` = `s`.`InfoDienstleisterID` AND `p`.`source` = `s`.`source`
SET `s`.`slots_per_appointment` = `s`.`slots_per_appointment` * (
    CAST(JSON_UNQUOTE(JSON_EXTRACT(`p`.`data`, '$.slotTimeInMinutes')) AS UNSIGNED) DIV 5
)
WHERE `s`.`slots_per_appointment` > 0
  AND JSON_VALID(`p`.`data`)
  AND CAST(JSON_UNQUOTE(JSON_EXTRACT(`p`.`data`, '$.slotTimeInMinutes')) AS UNSIGNED) > 5
  AND MOD(CAST(JSON_UNQUOTE(JSON_EXTRACT(`p`.`data`, '$.slotTimeInMinutes')) AS UNSIGNED), 5) = 0;

UPDATE `request_provider` `rp`
INNER JOIN `provider` `p` ON `p`.`id` = `rp`.`provider__id` AND `p`.`source` = `rp`.`source`
SET `rp`.`slots` = `rp`.`slots` * (
    CAST(JSON_UNQUOTE(JSON_EXTRACT(`p`.`data`, '$.slotTimeInMinutes')) AS UNSIGNED) DIV 5
)
WHERE JSON_VALID(`p`.`data`)
  AND CAST(JSON_UNQUOTE(JSON_EXTRACT(`p`.`data`, '$.slotTimeInMinutes')) AS UNSIGNED) > 5
  AND MOD(CAST(JSON_UNQUOTE(JSON_EXTRACT(`p`.`data`, '$.slotTimeInMinutes')) AS UNSIGNED), 5) = 0
  AND `rp`.`slots` > 0;

UPDATE `provider`
SET `data` = JSON_SET(`data`, '$.slotTimeInMinutes', 5)
WHERE JSON_VALID(`data`)
  AND CAST(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.slotTimeInMinutes')) AS UNSIGNED) > 5
  AND MOD(CAST(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.slotTimeInMinutes')) AS UNSIGNED), 5) = 0;

INSERT INTO `availability_history` (
    `scope_id`,
    `availability_id`,
    `action`,
    `start_date`,
    `end_date`,
    `every_x_weeks`,
    `every_other_week`,
    `weekday`,
    `start_time`,
    `appointment_start_time`,
    `end_time`,
    `appointment_end_time`,
    `time_slot`,
    `workstation_count`,
    `appointment_workstation_count`,
    `comment`,
    `internet_reduction`,
    `multiple_slots_allowed`,
    `open_from_days`,
    `open_until_days`,
    `version`,
    `changed_by`
)
SELECT `scope_id`,
       `OeffnungszeitID`,
       'dldb_slot_update',
       `start_date`,
       `end_date`,
       `every_x_weeks`,
       `every_other_week`,
       `weekday`,
       `start_time`,
       `appointment_start_time`,
       `end_time`,
       `appointment_end_time`,
       '00:05:00',
       0,
       `appointment_workstation_count`,
       LEFT(`comment`, 200),
       `internet_reduction`,
       `multiple_slots_allowed`,
       `open_from_days`,
       `open_until_days`,
       IFNULL(`version`, 0) + 1,
       'dldb'
FROM `oeffnungszeit`
WHERE TIME_TO_SEC(`time_slot`) > 0
  AND `time_slot` <> '00:05:00'
  AND MOD(TIME_TO_SEC(`time_slot`), 300) = 0;

UPDATE `oeffnungszeit`
SET `time_slot` = '00:05:00',
    `version` = IFNULL(`version`, 0) + 1
WHERE TIME_TO_SEC(`time_slot`) > 0
  AND `time_slot` <> '00:05:00'
  AND MOD(TIME_TO_SEC(`time_slot`), 300) = 0;

COMMIT;
