START TRANSACTION;

-- slots_per_appointment counted slots. max_appointment_length_in_minutes is that
-- same budget in minutes: the count times the slot length it was counted in.
-- The series with the latest start holds that length. After a split the cap was
-- already rescaled into the new series, and that series starts latest.
-- A Standort with no positive opening uses the provider slot length.
-- Empty and 0 stay empty.

UPDATE `standort` AS `s`
INNER JOIN (
    SELECT
        `o`.`scope_id`,
        MAX(TIME_TO_SEC(`o`.`time_slot`) DIV 60) AS `slot_minutes`
    FROM `oeffnungszeit` AS `o`
    INNER JOIN (
        SELECT `scope_id`, MAX(`start_date`) AS `latest_start`
        FROM `oeffnungszeit`
        WHERE TIME_TO_SEC(`time_slot`) > 0
        GROUP BY `scope_id`
    ) AS `latest`
        ON `latest`.`scope_id` = `o`.`scope_id`
       AND `o`.`start_date` = `latest`.`latest_start`
    WHERE TIME_TO_SEC(`o`.`time_slot`) > 0
    GROUP BY `o`.`scope_id`
) AS `factor`
    ON `factor`.`scope_id` = `s`.`StandortID`
SET `s`.`slots_per_appointment` = `s`.`slots_per_appointment` * `factor`.`slot_minutes`
WHERE `s`.`slots_per_appointment` IS NOT NULL
  AND `s`.`slots_per_appointment` > 0;

UPDATE `standort` AS `s`
INNER JOIN `provider` AS `p`
    ON `p`.`id` = `s`.`InfoDienstleisterID`
   AND `p`.`source` = `s`.`source`
SET `s`.`slots_per_appointment` = `s`.`slots_per_appointment` * CAST(
    JSON_UNQUOTE(JSON_EXTRACT(`p`.`data`, '$.slotTimeInMinutes')) AS UNSIGNED
)
WHERE `s`.`slots_per_appointment` IS NOT NULL
  AND `s`.`slots_per_appointment` > 0
  AND CAST(JSON_UNQUOTE(JSON_EXTRACT(`p`.`data`, '$.slotTimeInMinutes')) AS UNSIGNED) > 0
  AND NOT EXISTS (
      SELECT 1
      FROM `oeffnungszeit` AS `o`
      WHERE `o`.`scope_id` = `s`.`StandortID`
        AND TIME_TO_SEC(`o`.`time_slot`) > 0
  );

UPDATE `preferences` AS `p`
INNER JOIN `standort` AS `s`
    ON `s`.`StandortID` = `p`.`id`
SET `p`.`value` = IF(
        `s`.`slots_per_appointment` IS NULL OR `s`.`slots_per_appointment` = 0,
        '',
        CAST(`s`.`slots_per_appointment` AS CHAR)
    ),
    `p`.`name` = 'maxAppointmentLengthInMinutes',
    `p`.`updateTimestamp` = NOW()
WHERE `p`.`entity` = 'scope'
  AND `p`.`groupName` = 'client'
  AND `p`.`name` = 'slotsPerAppointment';

ALTER TABLE `standort`
    CHANGE COLUMN `slots_per_appointment` `max_appointment_length_in_minutes` INT(5) DEFAULT NULL;

COMMIT;
