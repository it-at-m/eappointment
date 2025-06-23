DELETE FROM gesamtkalender;

INSERT IGNORE INTO gesamtkalender
    (scope_id, availability_id, time, seat, status)
SELECT
    sl.scopeID,
    sl.availabilityID,
    DATE_ADD(
            CONCAT_WS(' ',
                      CONCAT(sl.year,'-',LPAD(sl.month,2,'0'),'-',LPAD(sl.day,2,'0')),
                      sl.time
            ),
            INTERVAL offs.n * 5 MINUTE
    ) AS time,
    seats.n   AS seat,
    'free'    AS status
FROM slot sl
    LEFT JOIN oeffnungszeit av ON av.OeffnungszeitID = sl.availabilityID
    JOIN (
    SELECT b.n * 4 + a.n AS n
    FROM (
    SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3
    ) AS a
    CROSS JOIN (
    SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2
    UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5
    ) AS b
    ) AS offs
    ON offs.n * 5 < sl.slotTimeInMinutes
    JOIN (
    SELECT a.n + b.n*10 + 1 AS n
    FROM (
    SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2
    UNION ALL SELECT 3 UNION ALL SELECT 4
    ) AS a
    CROSS JOIN (
    SELECT 0 AS n UNION ALL SELECT 1 UNION ALL SELECT 2
    UNION ALL SELECT 3 UNION ALL SELECT 4
    ) AS b
    WHERE a.n + b.n*10 < 50
    ) AS seats
    ON seats.n <= av.Anzahlterminarbeitsplaetze
WHERE av.Anzahlterminarbeitsplaetze IS NOT NULL AND av.Anzahlterminarbeitsplaetze > 0;
