START TRANSACTION;

-- ZMSKVR-1440: When slotsPerAppointment is set to a positive cap, it must allow
-- every bookable service at that scope at least once (relation slots × quantity 1).
-- Leave NULL and 0 unchanged (unset / treated as unlimited). Do not lower values
-- that are already at or above the required minimum.

UPDATE `standort` AS `s`
INNER JOIN (
    SELECT
        `rp`.`provider__id` AS `provider_id`,
        `rp`.`source` AS `source`,
        CEIL(MAX(`rp`.`slots`)) AS `min_slots_needed`
    FROM `request_provider` AS `rp`
    WHERE `rp`.`bookable` = 1
      AND `rp`.`slots` > 0
    GROUP BY `rp`.`provider__id`, `rp`.`source`
) AS `req`
    ON `req`.`provider_id` = `s`.`InfoDienstleisterID`
   AND `req`.`source` = `s`.`source`
SET `s`.`slots_per_appointment` = `req`.`min_slots_needed`
WHERE `s`.`slots_per_appointment` IS NOT NULL
  AND `s`.`slots_per_appointment` > 0
  AND `s`.`slots_per_appointment` < `req`.`min_slots_needed`;

UPDATE `preferences` AS `p`
INNER JOIN `standort` AS `s`
    ON `s`.`StandortID` = `p`.`id`
INNER JOIN (
    SELECT
        `rp`.`provider__id` AS `provider_id`,
        `rp`.`source` AS `source`,
        CEIL(MAX(`rp`.`slots`)) AS `min_slots_needed`
    FROM `request_provider` AS `rp`
    WHERE `rp`.`bookable` = 1
      AND `rp`.`slots` > 0
    GROUP BY `rp`.`provider__id`, `rp`.`source`
) AS `req`
    ON `req`.`provider_id` = `s`.`InfoDienstleisterID`
   AND `req`.`source` = `s`.`source`
SET `p`.`value` = CAST(`req`.`min_slots_needed` AS CHAR),
    `p`.`updateTimestamp` = NOW()
WHERE `p`.`entity` = 'scope'
  AND `p`.`groupName` = 'client'
  AND `p`.`name` = 'slotsPerAppointment'
  AND `p`.`value` REGEXP '^[0-9]+$'
  AND CAST(`p`.`value` AS UNSIGNED) > 0
  AND CAST(`p`.`value` AS UNSIGNED) < `req`.`min_slots_needed`;

COMMIT;
