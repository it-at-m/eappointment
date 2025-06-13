ALTER TABLE `nutzer` MODIFY COLUMN `SessionID` varchar(64) NOT NULL DEFAULT '';
ALTER TABLE `sessiondata` MODIFY COLUMN `sessionid` varchar(64) NOT NULL;

UPDATE `nutzer` SET `SessionID` = SHA2(`SessionID`, 256) WHERE `SessionID` != '';
UPDATE `sessiondata` SET `sessionid` = SHA2(`sessionid`, 256) WHERE `sessionid` != ''; 