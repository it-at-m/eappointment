-- EXPLAIN
SELECT
    COUNT(b.Datum),
    -- ((TIME_TO_SEC(o.Endzeit) - TIME_TO_SEC(o.Anfangszeit)) / TIME_TO_SEC(o.Timeslot)) * o.Anzahlterminarbeitsplaetze AS intern,
    -- ((TIME_TO_SEC(o.Endzeit) - TIME_TO_SEC(o.Anfangszeit)) / TIME_TO_SEC(o.Timeslot)) * (o.Anzahlterminarbeitsplaetze - o.reduktionTermineImInternet) AS public,
    -- ((TIME_TO_SEC(o.Endzeit) - TIME_TO_SEC(o.Anfangszeit)) / TIME_TO_SEC(o.Timeslot)) * (o.Anzahlterminarbeitsplaetze - o.reduktionTermineCallcenter) AS callcenter,
    b.Datum,
    b.Uhrzeit,
    o.Anzahlterminarbeitsplaetze AS slots_intern,
    o.Anzahlterminarbeitsplaetze - o.reduktionTermineImInternet AS slots_public,
    o.Anzahlterminarbeitsplaetze - o.reduktionTermineCallcenter AS slots_callcenter,
    FLOOR(((TIME_TO_SEC(b.Uhrzeit) - TIME_TO_SEC(o.Anfangszeit)) / TIME_TO_SEC(o.Timeslot))) AS slotnr,
    DAYNAME(b.Datum),
    o.*
FROM
    oeffnungszeit o
    LEFT JOIN buerger b ON
        b.StandortID = o.StandortID
        AND o.Wochentag & POW(2, DAYOFWEEK(b.Datum) - 1)
        AND b.Uhrzeit >= o.Anfangszeit
        AND b.Uhrzeit <= o.Endzeit
        AND b.Datum >= o.Startdatum
        AND b.Datum <= o.Endedatum
WHERE
    -- o.StandortID = 140
    o.StandortID IN (140, 160)
    AND b.Datum IS NOT NULL
    AND b.Datum >= "2016-01-18"
    AND b.Datum <= "2016-02-29"
GROUP BY o.OeffnungszeitID, b.Datum, slotnr
ORDER BY b.Datum, b.Uhrzeit ASC
