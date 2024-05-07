ALTER TABLE `buerger`
ADD COLUMN `temp_wartezeit` TIME DEFAULT NULL;

UPDATE `buerger`
SET `temp_wartezeit` = SEC_TO_TIME(`wartezeit` * 60);

ALTER TABLE `buerger`
DROP COLUMN `wartezeit`,
CHANGE COLUMN `temp_wartezeit` `wartezeit` TIME DEFAULT NULL;

ALTER TABLE `buergerarchiv`
MODIFY COLUMN `wartezeit` DOUBLE DEFAULT NULL;

ALTER TABLE `wartenrstatistik`
ADD COLUMN `temp_00_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_00_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_01_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_01_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_02_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_02_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_03_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_03_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_04_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_04_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_05_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_05_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_06_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_06_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_07_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_07_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_08_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_08_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_09_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_09_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_10_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_10_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_11_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_11_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_12_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_12_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_13_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_13_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_14_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_14_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_15_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_15_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_16_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_16_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_17_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_17_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_18_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_18_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_19_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_19_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_20_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_20_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_21_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_21_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_22_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_22_termin` TIME DEFAULT NULL,
ADD COLUMN `temp_23_spontan` TIME DEFAULT NULL,
ADD COLUMN `temp_23_termin` TIME DEFAULT NULL;

UPDATE `wartenrstatistik` SET
`temp_00_spontan` = SEC_TO_TIME(`echte_zeit_ab_00_spontan` * 60),
`temp_00_termin` = SEC_TO_TIME(`echte_zeit_ab_00_termin` * 60),
`temp_01_spontan` = SEC_TO_TIME(`echte_zeit_ab_01_spontan` * 60),
`temp_01_termin` = SEC_TO_TIME(`echte_zeit_ab_01_termin` * 60),
`temp_02_spontan` = SEC_TO_TIME(`echte_zeit_ab_02_spontan` * 60),
`temp_02_termin` = SEC_TO_TIME(`echte_zeit_ab_02_termin` * 60),
`temp_03_spontan` = SEC_TO_TIME(`echte_zeit_ab_03_spontan` * 60),
`temp_03_termin` = SEC_TO_TIME(`echte_zeit_ab_03_termin` * 60),
`temp_04_spontan` = SEC_TO_TIME(`echte_zeit_ab_04_spontan` * 60),
`temp_04_termin` = SEC_TO_TIME(`echte_zeit_ab_04_termin` * 60),
`temp_05_spontan` = SEC_TO_TIME(`echte_zeit_ab_05_spontan` * 60),
`temp_05_termin` = SEC_TO_TIME(`echte_zeit_ab_05_termin` * 60),
`temp_06_spontan` = SEC_TO_TIME(`echte_zeit_ab_06_spontan` * 60),
`temp_06_termin` = SEC_TO_TIME(`echte_zeit_ab_06_termin` * 60),
`temp_07_spontan` = SEC_TO_TIME(`echte_zeit_ab_07_spontan` * 60),
`temp_07_termin` = SEC_TO_TIME(`echte_zeit_ab_07_termin` * 60),
`temp_08_spontan` = SEC_TO_TIME(`echte_zeit_ab_08_spontan` * 60),
`temp_08_termin` = SEC_TO_TIME(`echte_zeit_ab_08_termin` * 60),
`temp_09_spontan` = SEC_TO_TIME(`echte_zeit_ab_09_spontan` * 60),
`temp_09_termin` = SEC_TO_TIME(`echte_zeit_ab_09_termin` * 60),
`temp_10_spontan` = SEC_TO_TIME(`echte_zeit_ab_10_spontan` * 60),
`temp_10_termin` = SEC_TO_TIME(`echte_zeit_ab_10_termin` * 60),
`temp_11_spontan` = SEC_TO_TIME(`echte_zeit_ab_11_spontan` * 60),
`temp_11_termin` = SEC_TO_TIME(`echte_zeit_ab_11_termin` * 60),
`temp_12_spontan` = SEC_TO_TIME(`echte_zeit_ab_12_spontan` * 60),
`temp_12_termin` = SEC_TO_TIME(`echte_zeit_ab_12_termin` * 60),
`temp_13_spontan` = SEC_TO_TIME(`echte_zeit_ab_13_spontan` * 60),
`temp_13_termin` = SEC_TO_TIME(`echte_zeit_ab_13_termin` * 60),
`temp_14_spontan` = SEC_TO_TIME(`echte_zeit_ab_14_spontan` * 60),
`temp_14_termin` = SEC_TO_TIME(`echte_zeit_ab_14_termin` * 60),
`temp_15_spontan` = SEC_TO_TIME(`echte_zeit_ab_15_spontan` * 60),
`temp_15_termin` = SEC_TO_TIME(`echte_zeit_ab_15_termin` * 60),
`temp_16_spontan` = SEC_TO_TIME(`echte_zeit_ab_16_spontan` * 60),
`temp_16_termin` = SEC_TO_TIME(`echte_zeit_ab_16_termin` * 60),
`temp_17_spontan` = SEC_TO_TIME(`echte_zeit_ab_17_spontan` * 60),
`temp_17_termin` = SEC_TO_TIME(`echte_zeit_ab_17_termin` * 60),
`temp_18_spontan` = SEC_TO_TIME(`echte_zeit_ab_18_spontan` * 60),
`temp_18_termin` = SEC_TO_TIME(`echte_zeit_ab_18_termin` * 60),
`temp_19_spontan` = SEC_TO_TIME(`echte_zeit_ab_19_spontan` * 60),
`temp_19_termin` = SEC_TO_TIME(`echte_zeit_ab_19_termin` * 60),
`temp_20_spontan` = SEC_TO_TIME(`echte_zeit_ab_20_spontan` * 60),
`temp_20_termin` = SEC_TO_TIME(`echte_zeit_ab_20_termin` * 60),
`temp_21_spontan` = SEC_TO_TIME(`echte_zeit_ab_21_spontan` * 60),
`temp_21_termin` = SEC_TO_TIME(`echte_zeit_ab_21_termin` * 60),
`temp_22_spontan` = SEC_TO_TIME(`echte_zeit_ab_22_spontan` * 60),
`temp_22_termin` = SEC_TO_TIME(`echte_zeit_ab_22_termin` * 60),
`temp_23_spontan` = SEC_TO_TIME(`echte_zeit_ab_23_spontan` * 60),
`temp_23_termin` = SEC_TO_TIME(`echte_zeit_ab_23_termin` * 60);

ALTER TABLE `wartenrstatistik`
DROP COLUMN `echte_zeit_ab_00_spontan`,
CHANGE COLUMN `temp_00_spontan` `echte_zeit_ab_00_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_00_termin`,
CHANGE COLUMN `temp_00_termin` `echte_zeit_ab_00_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_01_spontan`,
CHANGE COLUMN `temp_01_spontan` `echte_zeit_ab_01_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_01_termin`,
CHANGE COLUMN `temp_01_termin` `echte_zeit_ab_01_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_02_spontan`,
CHANGE COLUMN `temp_02_spontan` `echte_zeit_ab_02_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_02_termin`,
CHANGE COLUMN `temp_02_termin` `echte_zeit_ab_02_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_03_spontan`,
CHANGE COLUMN `temp_03_spontan` `echte_zeit_ab_03_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_03_termin`,
CHANGE COLUMN `temp_03_termin` `echte_zeit_ab_03_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_04_spontan`,
CHANGE COLUMN `temp_04_spontan` `echte_zeit_ab_04_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_04_termin`,
CHANGE COLUMN `temp_04_termin` `echte_zeit_ab_04_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_05_spontan`,
CHANGE COLUMN `temp_05_spontan` `echte_zeit_ab_05_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_05_termin`,
CHANGE COLUMN `temp_05_termin` `echte_zeit_ab_05_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_06_spontan`,
CHANGE COLUMN `temp_06_spontan` `echte_zeit_ab_06_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_06_termin`,
CHANGE COLUMN `temp_06_termin` `echte_zeit_ab_06_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_07_spontan`,
CHANGE COLUMN `temp_07_spontan` `echte_zeit_ab_07_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_07_termin`,
CHANGE COLUMN `temp_07_termin` `echte_zeit_ab_07_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_08_spontan`,
CHANGE COLUMN `temp_08_spontan` `echte_zeit_ab_08_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_08_termin`,
CHANGE COLUMN `temp_08_termin` `echte_zeit_ab_08_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_09_spontan`,
CHANGE COLUMN `temp_09_spontan` `echte_zeit_ab_09_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_09_termin`,
CHANGE COLUMN `temp_09_termin` `echte_zeit_ab_09_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_10_spontan`,
CHANGE COLUMN `temp_10_spontan` `echte_zeit_ab_10_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_10_termin`,
CHANGE COLUMN `temp_10_termin` `echte_zeit_ab_10_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_11_spontan`,
CHANGE COLUMN `temp_11_spontan` `echte_zeit_ab_11_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_11_termin`,
CHANGE COLUMN `temp_11_termin` `echte_zeit_ab_11_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_12_spontan`,
CHANGE COLUMN `temp_12_spontan` `echte_zeit_ab_12_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_12_termin`,
CHANGE COLUMN `temp_12_termin` `echte_zeit_ab_12_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_13_spontan`,
CHANGE COLUMN `temp_13_spontan` `echte_zeit_ab_13_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_13_termin`,
CHANGE COLUMN `temp_13_termin` `echte_zeit_ab_13_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_14_spontan`,
CHANGE COLUMN `temp_14_spontan` `echte_zeit_ab_14_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_14_termin`,
CHANGE COLUMN `temp_14_termin` `echte_zeit_ab_14_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_15_spontan`,
CHANGE COLUMN `temp_15_spontan` `echte_zeit_ab_15_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_15_termin`,
CHANGE COLUMN `temp_15_termin` `echte_zeit_ab_15_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_16_spontan`,
CHANGE COLUMN `temp_16_spontan` `echte_zeit_ab_16_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_16_termin`,
CHANGE COLUMN `temp_16_termin` `echte_zeit_ab_16_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_17_spontan`,
CHANGE COLUMN `temp_17_spontan` `echte_zeit_ab_17_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_17_termin`,
CHANGE COLUMN `temp_17_termin` `echte_zeit_ab_17_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_18_spontan`,
CHANGE COLUMN `temp_18_spontan` `echte_zeit_ab_18_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_18_termin`,
CHANGE COLUMN `temp_18_termin` `echte_zeit_ab_18_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_19_spontan`,
CHANGE COLUMN `temp_19_spontan` `echte_zeit_ab_19_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_19_termin`,
CHANGE COLUMN `temp_19_termin` `echte_zeit_ab_19_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_20_spontan`,
CHANGE COLUMN `temp_20_spontan` `echte_zeit_ab_20_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_20_termin`,
CHANGE COLUMN `temp_20_termin` `echte_zeit_ab_20_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_21_spontan`,
CHANGE COLUMN `temp_21_spontan` `echte_zeit_ab_21_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_21_termin`,
CHANGE COLUMN `temp_21_termin` `echte_zeit_ab_21_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_22_spontan`,
CHANGE COLUMN `temp_22_spontan` `echte_zeit_ab_22_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_22_termin`,
CHANGE COLUMN `temp_22_termin` `echte_zeit_ab_22_termin` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_23_spontan`,
CHANGE COLUMN `temp_23_spontan` `echte_zeit_ab_23_spontan` TIME DEFAULT NULL,
DROP COLUMN `echte_zeit_ab_23_termin`,
CHANGE COLUMN `temp_23_termin` `echte_zeit_ab_23_termin` TIME DEFAULT NULL;