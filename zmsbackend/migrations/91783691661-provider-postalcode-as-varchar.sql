-- Store provider postal codes as strings so leading zeros are preserved (e.g. 01067).
-- Previously INT(5) + intval() on write dropped leading zeros.

ALTER TABLE `provider`
  MODIFY COLUMN `contact__postalCode` VARCHAR(10) NOT NULL DEFAULT '';

-- Best-effort restore for German 5-digit PLZ that were stored as integers.
UPDATE `provider`
SET `contact__postalCode` = LPAD(`contact__postalCode`, 5, '0')
WHERE `contact__postalCode` REGEXP '^[0-9]+$'
  AND CHAR_LENGTH(`contact__postalCode`) < 5;
