-- Flyway migration: Munich standortcluster test data
-- Insert Munich-specific standortcluster (location clusters) for testing

INSERT IGNORE INTO `standortcluster` (`clusterID`, `name`, `clusterinfozeile1`, `clusterinfozeile2`, `stadtplanlink`, `aufrufanzeigetext`, `standortkuerzelanzeigen`) VALUES
(1, 'Versicherungsamt', 'Implerstr.11 - 4.OG - Raum 433', '', '', '', 0),
(3, 'Cluster-Allgemeinschalter', '', '', '', '', 0),
(6, 'Cluster-Maßnahmen', '', '', '', '', 0),
(9, 'Cluster-Maßnahmen', '', '', '', '', 0),
(12, 'Cluster-Maßnahmen', '', '', '', '', 0),
(13, 'Mietberatung', '', '', '', '', 0),
(16, 'Cluster Team A-D', '', '', '', '', 0),
(19, 'Bürgerbüro Leonrodstraße', '', '', '', '', 0),
(22, 'Cluster WB04', '', '', '', '', 0),
(25, 'WB03', '', '', '', '', 0),
(28, '', '', '', '', '', 0),
(31, 'Bürgerbüro Pasing', '', '', '', '', 0),
(34, 'Bürgerbüro Forstenrieder Allee', '', '', '', '', 0),
(37, 'Bürgerbüro Orleansplatz', '', '', '', '', 0),
(40, 'Bürgerbüro Riesenfeldstraße ALT', '', '', '', '', 0),
(43, '', '', '', '', '', 0),
(46, '', '', '', '', '', 0),
(48, 'Serviceschalter', '', '', '', '', 0),
(51, '', '', '', '', '', 0),
(53, 'Bürgerbüro Scheidplatz', '', '', '', '', 0);
