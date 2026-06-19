ALTER TABLE `buergerarchiv`
    ADD COLUMN `wegezeit` int(5) UNSIGNED DEFAULT NULL;

ALTER TABLE `buerger`
    ADD COLUMN `wegezeit` int(5) UNSIGNED DEFAULT NULL;