-- Create table with proper structure including primary key
DROP TABLE IF EXISTS buergerarchivtoday;
CREATE TABLE buergerarchivtoday (
  `BuergerarchivID` int(9) unsigned NOT NULL AUTO_INCREMENT,
  `StandortID` int(5) unsigned NOT NULL DEFAULT 0,
  `Datum` date NOT NULL DEFAULT '0000-00-00',
  `mitTermin` int(5) unsigned NOT NULL DEFAULT 0,
  `nicht_erschienen` int(2) unsigned NOT NULL DEFAULT 0,
  `Timestamp` time NOT NULL DEFAULT '00:00:00',
  `wartezeit` double DEFAULT 0,
  `AnzahlPersonen` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `bearbeitungszeit` double DEFAULT 0,
  `name` varchar(255) DEFAULT NULL,
  `dienstleistungen` varchar(1000) DEFAULT NULL,
  `wegezeit` int(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`BuergerarchivID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Insert data from buergerarchiv for today
INSERT INTO buergerarchivtoday
SELECT *
FROM buergerarchiv
WHERE `Datum` = CURDATE();

-- Index on Datum
CREATE INDEX idx_buergerarchivtoday_datum
    ON buergerarchivtoday (Datum);

-- Index on nicht_erschienen
CREATE INDEX idx_buergerarchivtoday_nicht_erschienen
    ON buergerarchivtoday (nicht_erschienen);

-- Index on mitTermin
CREATE INDEX idx_buergerarchivtoday_mitTermin
    ON buergerarchivtoday (mitTermin);

-- Index on StandortID
CREATE INDEX idx_buergerarchivtoday_standortid
    ON buergerarchivtoday (StandortID);

-- Index on wartezeit
CREATE INDEX idx_buergerarchivtoday_wartezeit
    ON buergerarchivtoday (wartezeit);

-- Index on scopedate (StandortID + Datum)
CREATE INDEX idx_buergerarchivtoday_scopedate
    ON buergerarchivtoday (StandortID, Datum);

-- Index on scopemissed (StandortID + nicht_erschienen)
CREATE INDEX idx_buergerarchivtoday_scopemissed
    ON buergerarchivtoday (StandortID, nicht_erschienen);

-- Index on scopeappointment (StandortID + mitTermin)
CREATE INDEX idx_buergerarchivtoday_scopeappointment
    ON buergerarchivtoday (StandortID, mitTermin);
