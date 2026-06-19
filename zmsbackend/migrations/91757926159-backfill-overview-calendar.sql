START TRANSACTION;

INSERT INTO overview_calendar (scope_id, process_id, status, starts_at, ends_at, updated_at)
SELECT r.StandortID,
       r.BuergerID,
       'confirmed', TIMESTAMP (r.Datum, r.Uhrzeit), DATE_ADD(
    TIMESTAMP (r.Datum, r.Uhrzeit), INTERVAL (1 + COALESCE (r.hatFolgetermine, 0)) * (
    SELECT s.slotTimeInMinutes
    FROM slot_process sp
    JOIN slot s ON s.slotID = sp.slotID
    WHERE sp.processID = r.BuergerID
    ORDER BY sp.updateTimestamp ASC, sp.slotID ASC
    LIMIT 1
    ) MINUTE
    ), r.updateTimestamp
FROM buerger r
WHERE r.status = 'confirmed'
  AND (r.istFolgeterminvon IS NULL
   OR r.istFolgeterminvon = 0)
  AND r.Datum >= CURDATE()
  AND EXISTS (
    SELECT 1 FROM slot_process sp
    JOIN slot s ON s.slotID = sp.slotID
    WHERE sp.processID = r.BuergerID
    )
ON DUPLICATE KEY
UPDATE
    scope_id =
VALUES (scope_id), status =
VALUES (status), starts_at =
VALUES (starts_at), ends_at =
VALUES (ends_at), updated_at =
VALUES (updated_at);

COMMIT;
