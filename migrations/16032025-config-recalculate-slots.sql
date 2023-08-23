INSERT INTO `config` (`name`, `value`) VALUES ("availability__calculateSlotsOnSave", "none")
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
INSERT INTO `config` (`name`, `value`) VALUES ("availability__calculateSlotsOnDemand", "none")
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
