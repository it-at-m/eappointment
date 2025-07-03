UPDATE buerger
SET `status` = CASE
    WHEN `Name` = '(abgesagt)' THEN 'deleted'
    WHEN `StandortID` = 0 AND `AbholortID` = 0 THEN 'blocked'
    WHEN `vorlaeufigeBuchung` = 1 AND `bestaetigt` = 0 THEN 'reserved'
    WHEN `nicht_erschienen` != 0 THEN 'missed'
    WHEN `parked` != 0 THEN 'parked'
    WHEN `Abholer` != 0 AND `AbholortID` != 0 AND `NutzerID` = 0 THEN 'pending'
    WHEN `AbholortID` != 0 AND `NutzerID` != 0 THEN 'pickup'
    WHEN `AbholortID` = 0 AND `aufruferfolgreich` != 0 AND `NutzerID` != 0 THEN 'processing'
    WHEN `aufrufzeit` != '00:00:00' AND `NutzerID` != 0 AND `AbholortID` = 0 THEN 'called'
    WHEN `Uhrzeit` = '00:00:00' THEN 'queued'
    WHEN `vorlaeufigeBuchung` = 0 AND `bestaetigt` = 0 THEN 'preconfirmed'
    WHEN `vorlaeufigeBuchung` = 0 AND `bestaetigt` = 1 THEN 'confirmed'
    ELSE 'free'
END
WHERE status IS NULL;