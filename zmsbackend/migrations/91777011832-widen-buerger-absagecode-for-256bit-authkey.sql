-- Widen absagecode to hold 256-bit hex tokens (64 chars); legacy short keys remain valid.
ALTER TABLE `buerger` MODIFY COLUMN `absagecode` varchar(64) DEFAULT NULL;