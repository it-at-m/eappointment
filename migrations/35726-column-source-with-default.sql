ALTER TABLE `standort`
ALTER COLUMN `source`
SET
DEFAULT 'dldb';
UPDATE `standort`
SET standort
.source = 'dldb';