ALTER TABLE `buergerarchiv`
    ADD COLUMN `wayTime` int(5) UNSIGNED DEFAULT NULL;

ALTER TABLE `buerger`
    ADD COLUMN `wayTime` int(5) UNSIGNED DEFAULT NULL;