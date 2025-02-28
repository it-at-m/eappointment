DROP TABLE IF EXISTS `closures`;
CREATE TABLE `closures` (
   `id` INT(5) UNSIGNED AUTO_INCREMENT,
   `year` SMALLINT(5),
   `month` TINYINT(5),
   `day` TINYINT(5),
   `StandortID` INT(5) UNSIGNED,
   `updateTimestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
   PRIMARY KEY (`id`),
   INDEX (StandortID),
   INDEX (StandortID, year, month, day)
)