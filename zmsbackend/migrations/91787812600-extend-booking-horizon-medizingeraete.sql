START TRANSACTION;

-- ZMSKVR-1260: 12-month booking horizon for Medizingerätemanagement (Branddirektion).
-- Standort-specific via Termine_bis / preferences.endInDaysDefault.
-- Other locations keep their own horizon; slot generation follows each bookable end.

UPDATE `standort`
SET `Termine_bis` = 365
WHERE `InfoDienstleisterID` IN ('10433938', '10433958');

UPDATE `preferences` AS p
INNER JOIN `standort` AS s ON p.`entity` = 'scope' AND p.`id` = s.`StandortID`
SET p.`value` = '365'
WHERE p.`groupName` = 'appointment'
  AND p.`name` = 'endInDaysDefault'
  AND s.`InfoDienstleisterID` IN ('10433938', '10433958');

UPDATE `oeffnungszeit` AS o
INNER JOIN `standort` AS s ON o.`scope_id` = s.`StandortID`
SET o.`open_until_days` = 365
WHERE s.`InfoDienstleisterID` IN ('10433938', '10433958')
  AND o.`open_until_days` >= 180;

COMMIT;
