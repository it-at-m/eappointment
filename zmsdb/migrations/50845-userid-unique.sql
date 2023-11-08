ALTER TABLE `nutzer` 
   DROP INDEX `Name`, 
   ADD UNIQUE KEY `Name` (`NAME`)