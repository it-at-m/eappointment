-- Contract phase. Run after the code that books every 5-minute step from the
-- first appointment row. hatFolgetermine on that row is the length. A follow-up
-- row's slot links are copied onto the first row, the missing steps are filled,
-- and the follow-up row is then removed. The process id stays in process_sequence
-- and can be used again.

START TRANSACTION;

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

COMMIT;
