INSERT INTO `config` (`name`, `value`) VALUES ("log__deleteOlderThanDays", 90)
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
