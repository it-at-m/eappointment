-- EXPLAIN
SELECT SUM(used), SUM(slots_intern), SUM(slots_public), SUM(slots_callcenter), SUM(free_public), SUM(free_callcenter), SUM(free_intern), Datum, daylabel, GROUP_CONCAT(DISTINCT StandortID) FROM (
SELECT
    COUNT(b.Datum) AS used,
    ((TIME_TO_SEC(o.Endzeit) - TIME_TO_SEC(o.Anfangszeit)) / TIME_TO_SEC(o.Timeslot)) * o.Anzahlterminarbeitsplaetze AS avail_intern,
    -- ((TIME_TO_SEC(o.Endzeit) - TIME_TO_SEC(o.Anfangszeit)) / TIME_TO_SEC(o.Timeslot)) * (o.Anzahlterminarbeitsplaetze - o.reduktionTermineImInternet) AS public,
    -- ((TIME_TO_SEC(o.Endzeit) - TIME_TO_SEC(o.Anfangszeit)) / TIME_TO_SEC(o.Timeslot)) * (o.Anzahlterminarbeitsplaetze - o.reduktionTermineCallcenter) AS callcenter,
    b.Datum,
    b.Uhrzeit,
    o.Anzahlterminarbeitsplaetze AS slots_intern,
    o.Anzahlterminarbeitsplaetze - o.reduktionTermineImInternet AS slots_public,
    o.Anzahlterminarbeitsplaetze - o.reduktionTermineCallcenter AS slots_callcenter,
    GREATEST(0, o.Anzahlterminarbeitsplaetze - o.reduktionTermineImInternet - COUNT(b.Datum)) AS free_public,
    GREATEST(0, o.Anzahlterminarbeitsplaetze - o.reduktionTermineCallcenter - COUNT(b.Datum)) AS free_callcenter,
    GREATEST(0, o.Anzahlterminarbeitsplaetze - COUNT(b.Datum)) AS free_intern,
    FLOOR(((TIME_TO_SEC(b.Uhrzeit) - TIME_TO_SEC(o.Anfangszeit)) / TIME_TO_SEC(o.Timeslot))) AS slotnr,
    DAYNAME(b.Datum) daylabel,
    o.StandortID
    -- o.*
FROM
    -- (SELECT * FROM oeffnungszeit WHERE StandortID IN (321,361,362,363,370,369,368,373,375,381,380,357,352,353,320,312,313,316,333,335,336,337,347,407,384,647,463,466,470,471,468,469,589,593,609,648,451,426,387,386,390,392,404,425,393,400,458,441,610,277,102,136,140)) o
    (SELECT * FROM oeffnungszeit WHERE StandortID IN (321,361,362,363,370,369,368,373,375,381,380,357,352,353,320,312,313,316,333,335,336,337,347,407,384,647,463,466,470,471,468,469,589,593,609,648,451,426,387,386,390,392,404,425,393,400,458,441,610,277,102,136,140,141,142,143,144,145,146,147,148,135,134,101,106,103,107,108,112,109,111,326,133,149,150,151,190,192,231,635,229,289,287,288,303,275,191,172,456,406,153,162,167,168,472,169,170,189,276)) o
    LEFT JOIN buerger b ON
        b.StandortID = o.StandortID
        AND o.Wochentag & POW(2, DAYOFWEEK(b.Datum) - 1)
        AND b.Uhrzeit >= o.Anfangszeit
        AND b.Uhrzeit <= o.Endzeit
        AND b.Datum >= o.Startdatum
        AND b.Datum <= o.Endedatum
WHERE
    -- o.StandortID = 140
    -- o.StandortID IN (140, 160)
    1
    AND b.Datum IS NOT NULL
    AND b.Datum >= "2016-01-18"
    AND b.Datum <= "2016-02-29"
GROUP BY o.OeffnungszeitID, b.Datum, slotnr
-- ORDER BY b.Datum, b.Uhrzeit ASC
) AS slots
GROUP BY Datum
ORDER BY Datum
