-- One pass onto a 5-minute slot. Opening hours, service slot counts, the
-- appointment cap, and provider slot length move together. A booked appointment
-- keeps its process number. hatFolgetermine is scaled so the length in minutes
-- stays the same on the 5-minute slot. No Folgetermin rows are inserted.
-- The slot grid is not rebuilt here. calculateSlots does that, one location
-- at a time, after this migration.

DROP TEMPORARY TABLE IF EXISTS `tmp_scope_minutes`;
DROP TEMPORARY TABLE IF EXISTS `tmp_parents`;

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

START TRANSACTION;

UPDATE `buerger` `b`
INNER JOIN `tmp_parents` `t` ON `t`.`parent_id` = `b`.`BuergerID`
SET `b`.`hatFolgetermine` = `t`.`steps` - 1
WHERE `b`.`hatFolgetermine` IS NULL OR `b`.`hatFolgetermine` <> `t`.`steps` - 1;

UPDATE `preferences` `pref`
INNER JOIN `standort` `s` ON `s`.`StandortID` = `pref`.`id`
INNER JOIN `tmp_scope_minutes` `m` ON `m`.`scope_id` = `s`.`StandortID`
SET `pref`.`value` = CAST(((`s`.`slots_per_appointment` * `m`.`minutes` + 4) DIV 5) AS CHAR)
WHERE `pref`.`entity` = 'scope'
  AND `pref`.`groupName` = 'client'
  AND `pref`.`name` = 'slotsPerAppointment'
  AND `m`.`kinds` = 1
  AND `m`.`minutes` > 5
  AND MOD(`m`.`minutes`, 5) = 0
  AND `s`.`slots_per_appointment` > 0;

INSERT INTO `preferences` (`entity`, `id`, `groupName`, `name`, `value`)
SELECT 'scope',
       `s`.`StandortID`,
       'client',
       'slotsPerAppointment',
       CAST(((`s`.`slots_per_appointment` * `m`.`minutes` + 4) DIV 5) AS CHAR)
FROM `standort` `s`
INNER JOIN `tmp_scope_minutes` `m` ON `m`.`scope_id` = `s`.`StandortID`
WHERE `m`.`kinds` = 1
  AND `m`.`minutes` > 5
  AND MOD(`m`.`minutes`, 5) = 0
  AND `s`.`slots_per_appointment` > 0
  AND NOT EXISTS (
      SELECT 1
      FROM `preferences` `pref`
      WHERE `pref`.`entity` = 'scope'
        AND `pref`.`id` = `s`.`StandortID`
        AND `pref`.`groupName` = 'client'
        AND `pref`.`name` = 'slotsPerAppointment'
  );

UPDATE `standort` `s`
INNER JOIN `tmp_scope_minutes` `m` ON `m`.`scope_id` = `s`.`StandortID`
SET `s`.`slots_per_appointment` = (`s`.`slots_per_appointment` * `m`.`minutes` + 4) DIV 5
WHERE `m`.`kinds` = 1
  AND `m`.`minutes` > 5
  AND MOD(`m`.`minutes`, 5) = 0
  AND `s`.`slots_per_appointment` > 0;

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
