-- Flyway migration: Munich organisation test data
-- Insert Munich-specific organisations for testing

INSERT IGNORE INTO `organisation` (`OrganisationsID`, `InfoBezirkID`, `KundenID`, `Organisationsname`, `Anschrift`, `kioskpasswortschutz`) VALUES
(3, 14, 1, 'Kreisverwaltungsreferat', '', 1),
(5, 14, 1, 'Sozialreferat', '', 0),
(7, 14, 1, 'Referat für Bildung und Sport', '', 0),
(10, 14, 1, 'Referat für Stadtplanung und Bauordnung ', 'Blumenstraße 28b', 0),
(11, 14, 1, 'Münchner Stadtentwässerung (MSE) ', 'Friedenstraße 40', 0),
(14, 14, 1, 'Personal- und Organisationsreferat', 'Rosenheimer Straße 118, 81669 München', 0);
