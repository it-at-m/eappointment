-- ZMSKVR-1710: Briefbüro service 1064278 goes from 15 to 20 minutes.
-- Scope 46, provider 10412449 (KfZ Zulassungsstelle Briefbüro). One service,
-- so the slot length becomes 20 minutes and a booking still occupies one slot.
--
-- The hourly import writes the new length onto the provider. It does not move
-- booked appointments, so a start on :15, :30 or :45 no longer matches a slot
-- and the old follow-up spacing (15 minutes) overlaps the longer appointment.
-- Upcoming appointments at this scope are placed on the 20-minute grid in the
-- order they were booked. A start that already falls on the grid stays there.
-- The next one starts at the later of its snapped time and the previous
-- appointment's end, so a longer booking does not overlap the one after it.
-- Past and cancelled appointments stay put (statistics and history).
-- Opening hours at the scope are set to 20 minutes and their version is
-- raised so the next calculateSlots rebuilds the grid.
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin.
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

DROP TEMPORARY TABLE IF EXISTS `tmp_zmskvr_1710_parent`;

CREATE TEMPORARY TABLE `tmp_zmskvr_1710_parent` (
    `parent_id` INT UNSIGNED NOT NULL PRIMARY KEY,
    `new_time` TIME NOT NULL
);

INSERT INTO `tmp_zmskvr_1710_parent` (`parent_id`, `new_time`)
WITH RECURSIVE `ordered` AS (
    SELECT
        `process`.`BuergerID` AS `parent_id`,
        `process`.`Datum` AS `day`,
        `process`.`Uhrzeit` AS `old_time`,
        IFNULL(`process`.`hatFolgetermine`, 0) + 1 AS `slots`,
        ROW_NUMBER() OVER (
            PARTITION BY `process`.`Datum`
            ORDER BY `process`.`Uhrzeit`, `process`.`BuergerID`
        ) AS `n`
    FROM `buerger` AS `process`
    INNER JOIN `standort` AS `scope`
        ON `scope`.`StandortID` = `process`.`StandortID`
    WHERE `scope`.`StandortID` = 46
      AND `scope`.`InfoDienstleisterID` = 10412449
      AND `scope`.`source` = 'dldb'
      AND (`process`.`istFolgeterminvon` IS NULL OR `process`.`istFolgeterminvon` = 0)
      AND `process`.`Name` <> '(abgesagt)'
      AND `process`.`Uhrzeit` > '00:00:00'
      AND (
          `process`.`Datum` > CURDATE()
          OR (`process`.`Datum` = CURDATE() AND `process`.`Uhrzeit` >= CURTIME())
      )
),
`placed` AS (
    SELECT
        `parent_id`,
        `day`,
        `slots`,
        `n`,
        CAST(LEAST(CEILING(TIME_TO_SEC(`old_time`) / 1200) * 1200, 85200) AS UNSIGNED) AS `new_seconds`
    FROM `ordered`
    WHERE `n` = 1
    UNION ALL
    SELECT
        `ordered`.`parent_id`,
        `ordered`.`day`,
        `ordered`.`slots`,
        `ordered`.`n`,
        CAST(LEAST(
            GREATEST(
                CEILING(TIME_TO_SEC(`ordered`.`old_time`) / 1200) * 1200,
                `placed`.`new_seconds` + `placed`.`slots` * 1200
            ),
            85200
        ) AS UNSIGNED) AS `new_seconds`
    FROM `ordered`
    INNER JOIN `placed`
        ON `placed`.`day` = `ordered`.`day`
       AND `ordered`.`n` = `placed`.`n` + 1
)
SELECT `parent_id`, SEC_TO_TIME(`new_seconds`)
FROM `placed`;

UPDATE `buerger` AS `process`
INNER JOIN `tmp_zmskvr_1710_parent` AS `placed`
    ON `placed`.`parent_id` = `process`.`BuergerID`
SET `process`.`Uhrzeit` = `placed`.`new_time`
WHERE `process`.`Uhrzeit` <> `placed`.`new_time`;

UPDATE `buerger` AS `followup`
INNER JOIN `tmp_zmskvr_1710_parent` AS `placed`
    ON `placed`.`parent_id` = `followup`.`istFolgeterminvon`
INNER JOIN `buerger` AS `parent`
    ON `parent`.`BuergerID` = `placed`.`parent_id`
INNER JOIN (
    SELECT
        `BuergerID`,
        ROW_NUMBER() OVER (
            PARTITION BY `istFolgeterminvon`
            ORDER BY `Uhrzeit`, `BuergerID`
        ) AS `step`
    FROM `buerger`
    WHERE `istFolgeterminvon` IS NOT NULL
      AND `istFolgeterminvon` <> 0
) AS `seq`
    ON `seq`.`BuergerID` = `followup`.`BuergerID`
SET `followup`.`Uhrzeit` = SEC_TO_TIME(
    LEAST(TIME_TO_SEC(`parent`.`Uhrzeit`) + `seq`.`step` * 1200, 85200)
)
WHERE `followup`.`Name` <> '(abgesagt)';

DROP TEMPORARY TABLE IF EXISTS `tmp_zmskvr_1710_parent`;

COMMIT;
