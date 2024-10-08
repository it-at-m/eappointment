ALTER TABLE `buerger`
ADD COLUMN `temp_wartezeit` TIME DEFAULT NULL;

UPDATE `buerger`
SET `temp_wartezeit` = SEC_TO_TIME(`wartezeit` * 60);

ALTER TABLE `buerger`
DROP COLUMN `wartezeit`,
CHANGE COLUMN `temp_wartezeit` `wartezeit` TIME DEFAULT NULL;

ALTER TABLE `buergerarchiv`
MODIFY COLUMN `wartezeit` DOUBLE DEFAULT 0.00;

ALTER TABLE `wartenrstatistik`
MODIFY COLUMN `echte_zeit_ab_00_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_00_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_00_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_00_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_01_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_01_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_01_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_01_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_02_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_02_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_02_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_02_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_03_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_03_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_03_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_03_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_04_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_04_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_04_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_04_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_05_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_05_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_05_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_05_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_06_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_06_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_06_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_06_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_07_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_07_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_07_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_07_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_08_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_08_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_08_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_08_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_09_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_09_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_09_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_09_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_10_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_10_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_10_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_10_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_11_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_11_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_11_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_11_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_12_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_12_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_12_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_12_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_13_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_13_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_13_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_13_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_14_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_14_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_14_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_14_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_15_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_15_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_15_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_15_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_16_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_16_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_16_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_16_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_17_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_17_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_17_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_17_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_18_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_18_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_18_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_18_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_19_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_19_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_19_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_19_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_20_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_20_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_20_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_20_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_21_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_21_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_21_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_21_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_22_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_22_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_22_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_22_termin` DOUBLE DEFAULT 0.00,

MODIFY COLUMN `echte_zeit_ab_23_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `echte_zeit_ab_23_termin` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_23_spontan` DOUBLE DEFAULT 0.00,
MODIFY COLUMN `zeit_ab_23_termin` DOUBLE DEFAULT 0.00;
