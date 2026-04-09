-- Rebuild statistik rows for a single broken date from source-of-truth tables.
-- Source-of-truth: buergerarchiv + buergeranliegen (+ lookups: standort, behoerde, clusterzuordnung).
-- Rule: buergerarchiv.nicht_erschienen = 1 must NOT be stored in statistik.

SET @fix_date := '2026-03-02';

DROP TEMPORARY TABLE IF EXISTS tmp_statistik_rebuild;

CREATE TEMPORARY TABLE tmp_statistik_rebuild AS
SELECT
    beh.KundenID                                AS kundenid,
    beh.OrganisationsID                         AS organisationsid,
    s.BehoerdenID                               AS behoerdenid,
    COALESCE(cz.clusterID, 0)                   AS clusterid,
    ba.StandortID                               AS standortid,
    COALESCE(ban.AnliegenID, -1)                AS anliegenid,
    ba.Datum                                    AS datum,
    ba.BuergerarchivID                          AS lastbuergerarchivid,
    CASE WHEN ba.mitTermin = 1 THEN 1 ELSE 0 END AS termin,
    s.InfoDienstleisterID                       AS info_dl_id,
    ba.bearbeitungszeit                         AS bearbeitungszeit
FROM buergerarchiv ba
LEFT JOIN buergeranliegen ban ON ban.BuergerarchivID = ba.BuergerarchivID
JOIN standort s          ON s.StandortID = ba.StandortID
JOIN behoerde beh        ON beh.BehoerdenID = s.BehoerdenID
LEFT JOIN clusterzuordnung cz ON cz.standortID = s.StandortID
WHERE ba.Datum = @fix_date
  AND COALESCE(ba.nicht_erschienen, 0) = 0;

-- Remove existing (messed up) stats for this date, then rebuild from scratch.
START TRANSACTION;

DELETE FROM statistik
WHERE datum = @fix_date;

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
    bearbeitungszeit
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
    bearbeitungszeit
FROM tmp_statistik_rebuild;

COMMIT;

DROP TEMPORARY TABLE IF EXISTS tmp_statistik_rebuild;

