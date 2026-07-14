-- Convert buerger.way_time from integer minutes to TIME (same temp-column swap as 91714158137 wartezeit).
-- Convert archive/statistic way_time fields to DOUBLE (91714158137 style).

ALTER TABLE `buerger`
ADD COLUMN `temp_way_time` TIME DEFAULT NULL;

UPDATE `buerger`
SET `temp_way_time` = SEC_TO_TIME(`way_time` * 60);

ALTER TABLE `buerger`
DROP COLUMN `way_time`,
CHANGE COLUMN `temp_way_time` `way_time` TIME DEFAULT NULL;

ALTER TABLE `buergerarchiv`
  MODIFY COLUMN `way_time` DOUBLE DEFAULT NULL;

ALTER TABLE `buergerarchivtoday`
  MODIFY COLUMN `way_time` DOUBLE DEFAULT NULL;

ALTER TABLE `wartenrstatistik`
  MODIFY COLUMN `hour_00_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_00_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_01_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_01_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_02_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_02_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_03_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_03_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_04_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_04_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_05_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_05_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_06_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_06_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_07_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_07_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_08_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_08_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_09_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_09_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_10_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_10_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_11_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_11_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_12_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_12_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_13_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_13_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_14_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_14_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_15_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_15_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_16_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_16_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_17_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_17_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_18_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_18_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_19_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_19_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_20_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_20_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_21_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_21_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_22_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_22_way_time_appointment` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_23_way_time_spontaneous` DOUBLE DEFAULT 0.00,
  MODIFY COLUMN `hour_23_way_time_appointment` DOUBLE DEFAULT 0.00;
