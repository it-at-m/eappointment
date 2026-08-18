-- Retention cleanup for availability_history (ZMSKVR-1249).
INSERT INTO `config` SET `name` = "cron__deleteOldAvailabilityHistory", `value` = "prod,stage,dev";
INSERT INTO `config` SET `name` = "availability_history__deleteOlderThanDays", `value` = "180";
