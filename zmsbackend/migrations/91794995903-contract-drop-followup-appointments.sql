-- Contract phase. Run after the code that books every 5-minute step from the
-- first appointment row. hatFolgetermine on that row is the length. A follow-up
-- row's slot links are copied onto the first row, the missing steps are filled,
-- and the follow-up row is then removed. The process id stays in process_sequence
-- and can be used again.
-- ZMSKVR-1627

DROP TEMPORARY TABLE IF EXISTS `tmp_contract_buerger`;
DROP TEMPORARY TABLE IF EXISTS `tmp_lock`;

CREATE TEMPORARY TABLE `tmp_contract_buerger` (
    `buerger_id` INT UNSIGNED NOT NULL PRIMARY KEY
);

INSERT INTO `tmp_contract_buerger` (`buerger_id`)
SELECT `b`.`BuergerID`
FROM `buerger` `b`
WHERE `b`.`istFolgeterminvon` IS NOT NULL
  AND `b`.`istFolgeterminvon` <> 0;

INSERT IGNORE INTO `tmp_contract_buerger` (`buerger_id`)
SELECT `b`.`istFolgeterminvon`
FROM `buerger` `b`
WHERE `b`.`istFolgeterminvon` IS NOT NULL
  AND `b`.`istFolgeterminvon` <> 0;

-- 100000000 is the highest innodb_lock_wait_timeout MariaDB 10.11 accepts.
-- 31536000 is the maximum lock_wait_timeout. A lock this session needs is
-- waited out until the holder commits. The inserts below take those locks
-- before any row is changed. The rows are discarded; the locks stay until COMMIT.
SET SESSION innodb_lock_wait_timeout = 100000000;
SET SESSION lock_wait_timeout = 31536000;
SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED;

-- The inserted value is always 1. row_id exists because the cluster rejects a table without a primary key.
CREATE TEMPORARY TABLE `tmp_lock` (
    `row_id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `id` INT UNSIGNED NOT NULL
);

START TRANSACTION;

INSERT INTO `tmp_lock` (`id`)
SELECT 1
FROM `buerger` `b`
INNER JOIN `tmp_contract_buerger` `chosen` ON `chosen`.`buerger_id` = `b`.`BuergerID`
ORDER BY `b`.`BuergerID`
FOR UPDATE;

INSERT INTO `tmp_lock` (`id`)
SELECT 1
FROM `buergeranliegen` `ba`
INNER JOIN `buerger` `child` ON `child`.`BuergerID` = `ba`.`BuergerID`
WHERE `child`.`istFolgeterminvon` IS NOT NULL
  AND `child`.`istFolgeterminvon` <> 0
ORDER BY `ba`.`BuergeranliegenID`
FOR UPDATE;

INSERT INTO `tmp_lock` (`id`)
SELECT 1
FROM `slot` `s`
INNER JOIN `buerger` `b`
  ON `s`.`scopeID` = `b`.`StandortID`
 AND `s`.`status` IN ('free', 'full')
 AND `s`.`slotTimeInMinutes` = 5
 AND (
   (
     `s`.`year` = YEAR(`b`.`Datum`)
     AND `s`.`month` = MONTH(`b`.`Datum`)
     AND `s`.`day` = DAY(`b`.`Datum`)
     AND `s`.`time` < ADDTIME(
       `b`.`Uhrzeit`,
       SEC_TO_TIME((IFNULL(`b`.`hatFolgetermine`, 0) + 1) * 300)
     )
     AND ADDTIME(`s`.`time`, SEC_TO_TIME(300)) > `b`.`Uhrzeit`
   )
   OR (
     ADDTIME(
       `b`.`Uhrzeit`,
       SEC_TO_TIME((IFNULL(`b`.`hatFolgetermine`, 0) + 1) * 300)
     ) >= '24:00:00'
     AND `s`.`year` = YEAR(DATE_ADD(`b`.`Datum`, INTERVAL 1 DAY))
     AND `s`.`month` = MONTH(DATE_ADD(`b`.`Datum`, INTERVAL 1 DAY))
     AND `s`.`day` = DAY(DATE_ADD(`b`.`Datum`, INTERVAL 1 DAY))
     AND `s`.`time` < SUBTIME(
       ADDTIME(
         `b`.`Uhrzeit`,
         SEC_TO_TIME((IFNULL(`b`.`hatFolgetermine`, 0) + 1) * 300)
       ),
       '24:00:00'
     )
   )
 )
WHERE (`b`.`istFolgeterminvon` IS NULL OR `b`.`istFolgeterminvon` = 0)
  AND IFNULL(`b`.`hatFolgetermine`, 0) > 0
  AND (`b`.`status` IS NULL OR `b`.`status` <> 'deleted')
  AND EXISTS (
    SELECT 1
    FROM `buerger` `child`
    WHERE `child`.`istFolgeterminvon` = `b`.`BuergerID`
  )
ORDER BY `s`.`slotID`
FOR UPDATE;

INSERT INTO `tmp_lock` (`id`)
SELECT 1
FROM `slot_process` `sp`
INNER JOIN `tmp_contract_buerger` `chosen` ON `chosen`.`buerger_id` = `sp`.`processID`
ORDER BY `sp`.`slotID`, `sp`.`processID`
FOR UPDATE;

INSERT IGNORE INTO `slot_process` (`slotID`, `processID`, `updateTimestamp`)
SELECT `copied`.`slotID`, `copied`.`processID`, `copied`.`updateTimestamp`
FROM (
    SELECT `sp`.`slotID`, `child`.`istFolgeterminvon` AS `processID`, NOW() AS `updateTimestamp`
    FROM `slot_process` `sp`
    INNER JOIN `buerger` `child` ON `child`.`BuergerID` = `sp`.`processID`
    INNER JOIN `buerger` `parent` ON `parent`.`BuergerID` = `child`.`istFolgeterminvon`
    WHERE `child`.`istFolgeterminvon` IS NOT NULL
      AND `child`.`istFolgeterminvon` <> 0
      AND (`parent`.`istFolgeterminvon` IS NULL OR `parent`.`istFolgeterminvon` = 0)
) `copied`;

INSERT IGNORE INTO `slot_process` (`slotID`, `processID`, `updateTimestamp`)
SELECT `s`.`slotID`, `b`.`BuergerID`, NOW()
FROM `buerger` `b`
INNER JOIN `slot` `s`
  ON `s`.`scopeID` = `b`.`StandortID`
 AND `s`.`status` IN ('free', 'full')
 AND `s`.`slotTimeInMinutes` = 5
 AND (
   (
     `s`.`year` = YEAR(`b`.`Datum`)
     AND `s`.`month` = MONTH(`b`.`Datum`)
     AND `s`.`day` = DAY(`b`.`Datum`)
     AND `s`.`time` < ADDTIME(
       `b`.`Uhrzeit`,
       SEC_TO_TIME((IFNULL(`b`.`hatFolgetermine`, 0) + 1) * 300)
     )
     AND ADDTIME(`s`.`time`, SEC_TO_TIME(300)) > `b`.`Uhrzeit`
   )
   OR (
     ADDTIME(
       `b`.`Uhrzeit`,
       SEC_TO_TIME((IFNULL(`b`.`hatFolgetermine`, 0) + 1) * 300)
     ) >= '24:00:00'
     AND `s`.`year` = YEAR(DATE_ADD(`b`.`Datum`, INTERVAL 1 DAY))
     AND `s`.`month` = MONTH(DATE_ADD(`b`.`Datum`, INTERVAL 1 DAY))
     AND `s`.`day` = DAY(DATE_ADD(`b`.`Datum`, INTERVAL 1 DAY))
     AND `s`.`time` < SUBTIME(
       ADDTIME(
         `b`.`Uhrzeit`,
         SEC_TO_TIME((IFNULL(`b`.`hatFolgetermine`, 0) + 1) * 300)
       ),
       '24:00:00'
     )
   )
 )
WHERE (`b`.`istFolgeterminvon` IS NULL OR `b`.`istFolgeterminvon` = 0)
  AND IFNULL(`b`.`hatFolgetermine`, 0) > 0
  AND (`b`.`status` IS NULL OR `b`.`status` <> 'deleted')
  AND EXISTS (
    SELECT 1
    FROM `buerger` `child`
    WHERE `child`.`istFolgeterminvon` = `b`.`BuergerID`
  );

DELETE `sp`
FROM `slot_process` `sp`
INNER JOIN `buerger` `child` ON `child`.`BuergerID` = `sp`.`processID`
WHERE `child`.`istFolgeterminvon` IS NOT NULL
  AND `child`.`istFolgeterminvon` <> 0;

DELETE `ba`
FROM `buergeranliegen` `ba`
INNER JOIN `buerger` `child` ON `child`.`BuergerID` = `ba`.`BuergerID`
WHERE `child`.`istFolgeterminvon` IS NOT NULL
  AND `child`.`istFolgeterminvon` <> 0;

DELETE FROM `buerger`
WHERE `istFolgeterminvon` IS NOT NULL
  AND `istFolgeterminvon` <> 0;

DROP TEMPORARY TABLE IF EXISTS `tmp_contract_buerger`;
DROP TEMPORARY TABLE IF EXISTS `tmp_lock`;

COMMIT;
