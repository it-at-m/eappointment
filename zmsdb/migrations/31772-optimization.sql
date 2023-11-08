ALTER TABLE `sms` ADD INDEX `BehoerdenID` (`BehoerdenID`);
ALTER TABLE `email` ADD INDEX `BehoerdenID` (`BehoerdenID`);
ALTER TABLE `provider` ADD INDEX `id` (`id`);
ALTER TABLE `request` ADD INDEX `id` (`id`);
ALTER TABLE `nutzer` ADD INDEX `Name` (`Name`);
ALTER TABLE `buerger` ADD INDEX `NutzerID` (`NutzerID`);
ALTER TABLE `kundenlinks` ADD INDEX `behoerdenid` (`behoerdenid`);
