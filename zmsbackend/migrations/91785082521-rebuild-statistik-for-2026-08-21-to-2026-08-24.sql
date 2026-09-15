-- ZMSKVR-1606: Rebuild statistik (Dienstleistungsstatistik) for 2026-08-21 .. 2026-08-24.
-- archiveStatisticData fatally failed after Muc-47 (bool vs preg_grep array), so no new
-- statistik rows were written. Source-of-truth is unchanged: buergerarchiv + buergeranliegen
-- (+ lookups: standort, behoerde, clusterzuordnung).
--
-- Same rebuild as 91775568665 / 91780610000 step 5, current column name processing_time
-- (91775568666). Rule: buergerarchiv.nicht_erschienen = 1 must NOT be stored in statistik.
-- COALESCE(AnliegenID, -1) keeps "nicht erbracht" (0) distinct from "nicht erfasst" (-1).

SET @from_date := '2026-08-21';
SET @to_date := '2026-08-24';

DROP TEMPORARY TABLE IF EXISTS tmp_statistik_rebuild;

CREATE TEMPORARY TABLE tmp_statistik_rebuild (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    kundenid INT(5) UNSIGNED NOT NULL,
    organisationsid INT(5) UNSIGNED NOT NULL,
    behoerdenid INT(5) UNSIGNED NOT NULL,
    clusterid INT(5) UNSIGNED NOT NULL,
    standortid INT(5) UNSIGNED NOT NULL,
    anliegenid INT(11) NOT NULL,
    datum DATE NOT NULL,
    lastbuergerarchivid INT(5) UNSIGNED NOT NULL,
    termin TINYINT(1) NOT NULL DEFAULT 0,
    info_dl_id INT(5) UNSIGNED NOT NULL,
    processing_time DOUBLE DEFAULT 0,
    PRIMARY KEY (id)
);

INSERT INTO tmp_statistik_rebuild (
    kundenid,
    organisationsid,
    behoerdenid,
    clusterid,
    standortid,
    anliegenid,
    datum,
    lastbuergerarchivid,
    termin,
    info_dl_id,
    processing_time
)
SELECT
    beh.KundenID                                 AS kundenid,
    beh.OrganisationsID                          AS organisationsid,
    s.BehoerdenID                                AS behoerdenid,
    COALESCE(cz.clusterID, 0)                    AS clusterid,
    ba.StandortID                                AS standortid,
    COALESCE(ban.AnliegenID, -1)                 AS anliegenid,
    ba.Datum                                     AS datum,
    ba.BuergerarchivID                           AS lastbuergerarchivid,
    CASE WHEN ba.mitTermin = 1 THEN 1 ELSE 0 END AS termin,
    s.InfoDienstleisterID                        AS info_dl_id,
    ba.processing_time                           AS processing_time
FROM buergerarchiv ba
LEFT JOIN buergeranliegen ban ON ban.BuergerarchivID = ba.BuergerarchivID
JOIN standort s          ON s.StandortID = ba.StandortID
JOIN behoerde beh        ON beh.BehoerdenID = s.BehoerdenID
LEFT JOIN clusterzuordnung cz ON cz.standortID = s.StandortID
WHERE ba.Datum BETWEEN @from_date AND @to_date
  AND COALESCE(ba.nicht_erschienen, 0) = 0;

START TRANSACTION;

DELETE FROM statistik
WHERE datum BETWEEN @from_date AND @to_date;

INSERT INTO statistik (
    kundenid,
    organisationsid,
    behoerdenid,
    clusterid,
    standortid,
    anliegenid,
    datum,
    lastbuergerarchivid,
    termin,
    info_dl_id,
    processing_time
)
SELECT
    kundenid,
    organisationsid,
    behoerdenid,
    clusterid,
    standortid,
    anliegenid,
    datum,
    lastbuergerarchivid,
    termin,
    info_dl_id,
    processing_time
FROM tmp_statistik_rebuild;

COMMIT;

DROP TEMPORARY TABLE IF EXISTS tmp_statistik_rebuild;
