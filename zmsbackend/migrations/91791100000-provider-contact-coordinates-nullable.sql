-- A location without coordinates is still a location. The hourly import stores
-- geo as null and must be able to write that row.

ALTER TABLE `provider`
  MODIFY COLUMN `contact__lat` FLOAT NULL DEFAULT NULL,
  MODIFY COLUMN `contact__lon` FLOAT NULL DEFAULT NULL;
