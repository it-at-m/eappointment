-- Values of 1 block citizen reschedule (needs room for the temporary second booking).
UPDATE `standort`
SET `appointments_per_mail` = 2
WHERE `appointments_per_mail` = 1;
