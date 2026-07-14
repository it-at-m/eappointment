CREATE TABLE `apiclient` ( 

    `apiClientID` INT(5) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
    `clientKey` VARCHAR(32) NOT NULL, 
    `shortname` VARCHAR(32) NOT NULL, 
    `accesslevel` ENUM("public", "callcenter", "intern", "blocked") DEFAULT "public",
    `updateTimestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX (`clientKey`, `accesslevel`)
) 
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

INSERT INTO `apiclient` SET `apiClientID` = 1, `clientKey` = 'default', `shortname` = 'default', `accesslevel` = 'public';
INSERT INTO `apiclient` SET `apiClientID` = 2, `clientKey` = '8pnaRHkUBYJqz9i9NPDEeZq6mUDMyRHE', `shortname` = 'test', `accesslevel` = 'blocked';


ALTER TABLE `buerger`
    ADD COLUMN `apiClientID` INT(5) UNSIGNED DEFAULT '1';

ALTER TABLE `apikey`
    ADD COLUMN `apiClientID` INT(5) UNSIGNED DEFAULT '1';
