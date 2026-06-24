-- Backfill indexed search columns from the legacy JSON `data` blob.
-- Idempotent: only updates rows that have not been backfilled yet (client_name IS NULL).
-- For very large tables (1M+ rows), if this times out during deploy, skip by marking
-- the migration applied manually and run: php bin/backfillLogColumns --batch=5000 --commit

UPDATE `log`
SET
    `action` = CASE JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Aktion'))
        WHEN 'E-Mail-Versand erfolgreich' THEN 'mail_success'
        WHEN 'E-Mail-Versand ist fehlgeschlagen' THEN 'mail_fail'
        WHEN 'Terminstatus wurde geändert' THEN 'status_changed'
        WHEN 'Erinnerungsmail wurde gesendet' THEN 'reminder_sent'
        WHEN 'Termin aus der Warteschlange entfernt' THEN 'removed_from_queue'
        WHEN 'Termin wurde aufgerufen' THEN 'called'
        WHEN 'Termin wurde archiviert' THEN 'archived'
        WHEN 'Termin wurde geändert' THEN 'edited'
        WHEN 'Termin wurde weitergeleitet' THEN 'redirected'
        WHEN 'Neuer Termin wurde erstellt' THEN 'created'
        WHEN 'Termin wurde gelöscht' THEN 'deleted'
        WHEN 'Termin wurde abgesagt' THEN 'canceled'
        ELSE NULL
    END,
    `display_number` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Terminnummer')), ''),
    `queue_number` = CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Wartenummer')), '') AS UNSIGNED),
    `appointment_at` = STR_TO_DATE(
        NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Terminzeit')), ''),
        '%d.%m.%Y %H:%i:%s'
    ),
    `slot_count` = CAST(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Slots')), '') AS UNSIGNED),
    `client_name` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$."Bürger*in"')), ''),
    `services` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Dienstleistungen')), ''),
    `scope_name` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Standort')), ''),
    `client_email` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.E-Mail')), ''),
    `client_phone` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Telefon')), ''),
    `process_status` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$.Status')), ''),
    `db_status` = NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$."DB Status"')), '')
WHERE `type` = 'buerger'
  AND `data` IS NOT NULL
  AND `data` != ''
  AND `client_name` IS NULL;
