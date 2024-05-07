ALTER TABLE `buerger`
ADD COLUMN `temp_wartezeit` TIME DEFAULT NULL;

UPDATE `buerger`
SET `temp_wartezeit` = SEC_TO_TIME(`wartezeit` * 60);

ALTER TABLE `buerger`
DROP COLUMN `wartezeit`,
CHANGE COLUMN `temp_wartezeit` `wartezeit` TIME DEFAULT NULL;

ALTER TABLE `buergerarchiv`
MODIFY COLUMN `wartezeit` DOUBLE DEFAULT NULL;