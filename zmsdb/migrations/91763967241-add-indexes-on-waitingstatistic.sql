ALTER TABLE `wartenrstatistik` DROP INDEX `scopedate`;

ALTER TABLE `wartenrstatistik`
  ADD INDEX `idx_standort_datum` (`standortid`, `datum`),
  ADD INDEX `idx_datum_standort` (`datum`, `standortid`),
  ADD INDEX `idx_datum` (`datum`),
  ADD INDEX `idx_standort` (`standortid`);