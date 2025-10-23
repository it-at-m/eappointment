CREATE TABLE buergerarchivtoday AS
SELECT *
FROM buergerarchiv
WHERE `Datum` = CURDATE();

-- Primary key
ALTER TABLE buergerarchivtoday
    ADD PRIMARY KEY (BuergerarchivID);

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
