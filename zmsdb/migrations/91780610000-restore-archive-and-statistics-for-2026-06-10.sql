-- Restore archive and customer statistics for 2026-06-10 after deleteAppointmentData cron failure.
--
-- 1) Fix dereference payload with StandortID NULL on the fix date (single known row)
-- 2) Remove partial buergerarchivtoday rows (~29) that already exist in buergerarchiv
-- 3) Insert remaining stranded buerger into buergerarchiv (skip rows already archived)
-- 4) Relink buergeranliegen and delete processed buerger rows
-- 5) Rebuild statistik for the fix date from buergerarchiv (ArchivedDataIntoStatisticByCron rules)

SET @fix_date := '2026-06-10';
SET @broken_scope_id := 199;
SET @sq := CHAR(39);
SET @deref_scope_key := CONCAT(@sq, 'StandortID', @sq, ' =>');
SET @deref_scope_null := CONCAT(@deref_scope_key, ' NULL');
SET @deref_scope_fixed := CONCAT(@deref_scope_key, ' ', @broken_scope_id);

DROP TEMPORARY TABLE IF EXISTS tmp_buerger_archive_source;
DROP TEMPORARY TABLE IF EXISTS tmp_archive_map;
DROP TEMPORARY TABLE IF EXISTS tmp_statistik_rebuild;

UPDATE buerger
SET Anmerkung = REPLACE(Anmerkung, @deref_scope_null, @deref_scope_fixed)
WHERE Datum = @fix_date
  AND Anmerkung LIKE CONCAT('%', @deref_scope_null, '%');

CREATE TEMPORARY TABLE tmp_buerger_archive_source (
    buerger_id INT UNSIGNED NOT NULL,
    scope_id INT UNSIGNED NOT NULL,
    name VARCHAR(255) DEFAULT NULL,
    dienstleistungen VARCHAR(1000) DEFAULT NULL,
    datum DATE NOT NULL,
    mit_termin TINYINT(1) NOT NULL DEFAULT 0,
    nicht_erschienen TINYINT(1) NOT NULL DEFAULT 0,
    arrival_timestamp TIME NOT NULL,
    waiting_time DOUBLE DEFAULT 0,
    way_time DOUBLE DEFAULT 0,
    processing_time DOUBLE DEFAULT 0,
    anzahl_personen TINYINT UNSIGNED NOT NULL DEFAULT 1,
    is_ticketprinter TINYINT(1) NOT NULL DEFAULT 0,
    existing_archive_id INT UNSIGNED DEFAULT NULL,
    PRIMARY KEY (buerger_id)
) ENGINE=Aria;

INSERT INTO tmp_buerger_archive_source (
    buerger_id,
    scope_id,
    name,
    dienstleistungen,
    datum,
    mit_termin,
    nicht_erschienen,
    arrival_timestamp,
    waiting_time,
    way_time,
    processing_time,
    anzahl_personen,
    is_ticketprinter
)
SELECT
    b.BuergerID AS buerger_id,
    CASE
        WHEN COALESCE(NULLIF(b.AbholortID, 0), NULLIF(b.StandortID, 0)) > 0
            THEN COALESCE(NULLIF(b.AbholortID, 0), b.StandortID)
        WHEN b.Anmerkung LIKE CONCAT('%', @deref_scope_key, '%')
            THEN CAST(
                NULLIF(
                    TRIM(BOTH @sq FROM TRIM(
                        SUBSTRING_INDEX(
                            TRIM(LEADING ' ' FROM SUBSTRING_INDEX(b.Anmerkung, @deref_scope_key, -1)),
                            ',',
                            1
                        )
                    )),
                    'NULL'
                ) AS UNSIGNED
            )
        WHEN b.custom_text_field LIKE CONCAT('%', @deref_scope_key, '%')
            THEN CAST(
                NULLIF(
                    TRIM(BOTH @sq FROM TRIM(
                        SUBSTRING_INDEX(
                            TRIM(LEADING ' ' FROM SUBSTRING_INDEX(b.custom_text_field, @deref_scope_key, -1)),
                            ',',
                            1
                        )
                    )),
                    'NULL'
                ) AS UNSIGNED
            )
        WHEN b.custom_text_field2 LIKE CONCAT('%', @deref_scope_key, '%')
            THEN CAST(
                NULLIF(
                    TRIM(BOTH @sq FROM TRIM(
                        SUBSTRING_INDEX(
                            TRIM(LEADING ' ' FROM SUBSTRING_INDEX(b.custom_text_field2, @deref_scope_key, -1)),
                            ',',
                            1
                        )
                    )),
                    'NULL'
                ) AS UNSIGNED
            )
        ELSE 0
    END AS scope_id,
    b.Name AS name,
    CASE
        WHEN req_cnt.req_count IS NULL OR req_cnt.req_count = 0 THEN ''
        WHEN req_cnt.req_count = 1 THEN r.name
        ELSE CONCAT(r.name, ' +', req_cnt.req_count - 1)
    END AS dienstleistungen,
    b.Datum AS datum,
    CASE WHEN b.Uhrzeit = '00:00:00' THEN 0 ELSE 1 END AS mit_termin,
    CASE
        WHEN COALESCE(b.nicht_erschienen, 0) != 0 THEN 1
        WHEN b.status = 'missed' THEN 1
        WHEN b.status IN ('confirmed', 'queued', 'called') THEN 1
        ELSE 0
    END AS nicht_erschienen,
    TIME(
        CASE
            WHEN b.wsm_aufnahmezeit IS NOT NULL AND b.wsm_aufnahmezeit != '00:00:00' THEN b.wsm_aufnahmezeit
            ELSE b.Uhrzeit
        END
    ) AS arrival_timestamp,
    GREATEST(COALESCE(b.waiting_time, 0), 0) AS waiting_time,
    GREATEST(COALESCE(b.way_time, 0), 0) AS way_time,
    CASE
        WHEN b.processing_time IS NULL OR b.processing_time = '00:00:00' THEN 0
        ELSE ROUND(TIME_TO_SEC(b.processing_time) / 60, 2)
    END AS processing_time,
    GREATEST(COALESCE(b.AnzahlPersonen, 1), 1) AS anzahl_personen,
    COALESCE(b.is_ticketprinter, 0) AS is_ticketprinter
FROM buerger b
LEFT JOIN (
    SELECT
        ban.BuergerID,
        MIN(ban.BuergeranliegenID) AS first_anliegen_id,
        COUNT(*) AS req_count
    FROM buergeranliegen ban
    WHERE ban.BuergerID > 0
    GROUP BY ban.BuergerID
) req_cnt ON req_cnt.BuergerID = b.BuergerID
LEFT JOIN buergeranliegen ban_first ON ban_first.BuergeranliegenID = req_cnt.first_anliegen_id
LEFT JOIN request r ON r.id = ban_first.AnliegenID AND r.source = ban_first.source
WHERE b.Datum = @fix_date
  AND (b.istFolgeterminvon IS NULL OR b.istFolgeterminvon = 0)
  AND b.Name NOT IN ('(abgesagt)')
  AND (
      b.status IN ('confirmed', 'queued', 'called', 'missed', 'parked', 'processing', 'pending')
      OR (b.status = 'blocked' AND b.Name = 'dereferenced')
  )
  AND EXISTS (
      SELECT 1
      FROM buergeranliegen ban
      WHERE ban.BuergerID = b.BuergerID
        AND COALESCE(ban.BuergerarchivID, 0) = 0
  );

UPDATE tmp_buerger_archive_source src
SET src.existing_archive_id = (
    SELECT MIN(ba.BuergerarchivID)
    FROM buergerarchiv ba
    WHERE ba.Datum = src.datum
      AND ba.name <=> src.name
      AND ba.StandortID = src.scope_id
      AND ba.Timestamp = src.arrival_timestamp
      AND ba.mitTermin = src.mit_termin
      AND ba.nicht_erschienen = src.nicht_erschienen
);

DELETE bat
FROM buergerarchivtoday bat
INNER JOIN tmp_buerger_archive_source src
    ON src.existing_archive_id IS NOT NULL
   AND bat.Datum = src.datum
   AND bat.name <=> src.name
   AND bat.StandortID = src.scope_id
   AND bat.Timestamp = src.arrival_timestamp
   AND bat.mitTermin = src.mit_termin
   AND bat.nicht_erschienen = src.nicht_erschienen
WHERE bat.Datum = @fix_date;

START TRANSACTION;

INSERT INTO buergerarchiv (
    StandortID,
    name,
    dienstleistungen,
    Datum,
    mitTermin,
    nicht_erschienen,
    Timestamp,
    waiting_time,
    way_time,
    processing_time,
    AnzahlPersonen,
    is_ticketprinter
)
SELECT
    scope_id,
    name,
    dienstleistungen,
    datum,
    mit_termin,
    nicht_erschienen,
    arrival_timestamp,
    waiting_time,
    way_time,
    processing_time,
    anzahl_personen,
    is_ticketprinter
FROM tmp_buerger_archive_source
WHERE scope_id > 0
  AND existing_archive_id IS NULL
ORDER BY buerger_id;

SET @first_archive_id := LAST_INSERT_ID();

CREATE TEMPORARY TABLE tmp_archive_map (
    buerger_id INT UNSIGNED NOT NULL,
    archive_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (buerger_id)
) ENGINE=Aria;

INSERT INTO tmp_archive_map (buerger_id, archive_id)
SELECT
    src.buerger_id,
    @first_archive_id + ROW_NUMBER() OVER (ORDER BY src.buerger_id) - 1 AS archive_id
FROM tmp_buerger_archive_source src
WHERE src.scope_id > 0
  AND src.existing_archive_id IS NULL;

INSERT INTO tmp_archive_map (buerger_id, archive_id)
SELECT
    src.buerger_id,
    src.existing_archive_id
FROM tmp_buerger_archive_source src
WHERE src.existing_archive_id IS NOT NULL;

UPDATE buergeranliegen ban
JOIN tmp_archive_map m ON m.buerger_id = ban.BuergerID
SET
    ban.BuergerarchivID = m.archive_id,
    ban.BuergerID = 0;

DELETE b
FROM buerger b
JOIN tmp_archive_map m ON m.buerger_id = b.BuergerID;

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
) ENGINE=Aria;

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
    beh.KundenID AS kundenid,
    beh.OrganisationsID AS organisationsid,
    s.BehoerdenID AS behoerdenid,
    COALESCE(cz.clusterID, 0) AS clusterid,
    ba.StandortID AS standortid,
    COALESCE(ban.AnliegenID, -1) AS anliegenid,
    ba.Datum AS datum,
    ba.BuergerarchivID AS lastbuergerarchivid,
    CASE WHEN ba.mitTermin = 1 THEN 1 ELSE 0 END AS termin,
    s.InfoDienstleisterID AS info_dl_id,
    ba.processing_time AS processing_time
FROM buergerarchiv ba
LEFT JOIN buergeranliegen ban ON ban.BuergerarchivID = ba.BuergerarchivID
JOIN standort s ON s.StandortID = ba.StandortID
JOIN behoerde beh ON beh.BehoerdenID = s.BehoerdenID
LEFT JOIN clusterzuordnung cz ON cz.standortID = s.StandortID
WHERE ba.Datum = @fix_date
  AND COALESCE(ba.nicht_erschienen, 0) = 0;

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

DROP TEMPORARY TABLE IF EXISTS tmp_buerger_archive_source;
DROP TEMPORARY TABLE IF EXISTS tmp_archive_map;
DROP TEMPORARY TABLE IF EXISTS tmp_statistik_rebuild;
