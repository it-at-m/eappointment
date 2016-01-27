-- EXPLAIN
-- SELECT SUM(used), SUM(slots_intern), SUM(slots_public), SUM(slots_callcenter), SUM(free_public), SUM(free_callcenter), SUM(free_intern), Datum, daylabel, GROUP_CONCAT(DISTINCT StandortID) FROM (
SELECT SQL_NO_CACHE
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
    o.*
FROM
    standort s
    LEFT JOIN oeffnungszeit o USING(StandortID)
    LEFT JOIN buerger b ON
        b.StandortID = o.StandortID
        AND o.Wochentag & POW(2, DAYOFWEEK(b.Datum) - 1)
        AND b.Uhrzeit >= o.Anfangszeit
        AND b.Uhrzeit <= o.Endzeit
        AND b.Datum >= o.Startdatum
        AND b.Datum <= o.Endedatum
WHERE
    o.StandortID = 160
    AND b.Datum IS NOT NULL
    AND b.Datum >= "2016-01-18"
    AND b.Datum <= "2016-02-29"
    AND o.Endedatum >= "2016-01-18"
    AND o.Startdatum <= "2016-02-29"
    AND o.Anzahlterminarbeitsplaetze != 0
GROUP BY o.OeffnungszeitID, b.Datum, slotnr
-- ORDER BY b.Datum, b.Uhrzeit ASC
-- ) AS slots GROUP BY Datum ORDER BY Datum
