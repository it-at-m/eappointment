-- 1 blocks citizen reschedule (needs room for the temporary second booking).
-- 0 is treated as unlimited already; normalize to NULL.
UPDATE `standort`
SET `appointments_per_mail` = 2
WHERE `appointments_per_mail` = 1;

UPDATE `standort`
SET `appointments_per_mail` = NULL
WHERE `appointments_per_mail` = 0;
