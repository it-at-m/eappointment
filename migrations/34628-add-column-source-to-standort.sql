ALTER TABLE `standort`
    ADD COLUMN `source` VARCHAR(10),
    ADD INDEX (`source`);
UPDATE `standort` SET standort.source = 'dldb';
