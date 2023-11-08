DROP TABLE IF EXISTS `notificationqueue`;
CREATE TABLE `notificationqueue` ( 
	`id` INT(5) UNSIGNED NOT NULL AUTO_INCREMENT , 
	`processID` INT(5) NOT NULL DEFAULT '0' , 
	`departmentID` INT(5) UNSIGNED NOT NULL DEFAULT '0' , 	
	`createIP` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL , 
	`createTimestamp` BIGINT(20) NOT NULL DEFAULT '0' , 		
	`message` VARCHAR(350) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`clientFamilyName` VARCHAR(150) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`clientTelephone` VARCHAR(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,   		
PRIMARY KEY (`id`)) 
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;
