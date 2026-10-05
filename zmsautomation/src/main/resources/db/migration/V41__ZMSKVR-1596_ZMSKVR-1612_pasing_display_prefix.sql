-- ZMSKVR-1596 / ZMSKVR-1612: Bürgerbüro Pasing shows a display number with the prefix P.
--
-- V6 stores P in the preferences table. Booking reads standort.display_number_prefix,
-- which V5 leaves empty, so the confirmation shows the process id and there is no
-- letter prefix to strip before the customer search.

UPDATE `standort`
SET `display_number_prefix` = 'P'
WHERE `StandortID` = 136
  AND (`display_number_prefix` IS NULL OR `display_number_prefix` = '');
