DROP TABLE IF EXISTS `mailqueue`;
CREATE TABLE `mailqueue` ( 
	`id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`processID` INT(5) NOT NULL DEFAULT '0' , 
	`departmentID` INT(5) UNSIGNED NOT NULL DEFAULT '0' , 	
	`createIP` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , 
	`createTimestamp` BIGINT(20) NOT NULL DEFAULT '0' , 		
	`subject` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`clientFamilyName` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`clientEmail` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,  
PRIMARY KEY (`id`)) 
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `mailpart`;
CREATE TABLE `mailpart` ( 
	`id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`queueId` INT(5) UNSIGNED NOT NULL DEFAULT '0' , 
	`mime` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , 
	`content` TEXT CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , 
	`base64` INT(1) UNSIGNED NOT NULL DEFAULT '0' , 
PRIMARY KEY (`id`)) 
ENGINE = InnoDB;
