-- Update all existing locations to have empty string for info_for_all_appointments
-- instead of the default value from the previous migration
UPDATE standort 
SET `info_for_all_appointments` = '' 
WHERE `info_for_all_appointments` = 'Bitte versuchen Sie es noch einmal zu einem späteren Zeitpunkt.';
