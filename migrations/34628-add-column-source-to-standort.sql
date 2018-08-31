ALTER TABLE `standort`
    ADD COLUMN `source` VARCHAR(10),
    ADD INDEX (`source`);
UPDATE `standort` LEFT JOIN provider ON standort.`InfoDienstleisterID` = provider.`id` SET standort.source = provider.source;
