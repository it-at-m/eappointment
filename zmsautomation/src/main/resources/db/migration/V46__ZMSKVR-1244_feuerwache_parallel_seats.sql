-- ZMSKVR-1244: extra internet seats for Föhring display and the Pasing later day.
-- Föhring (136520) is the Ort toggle / slot display office. Pasing (136521) is the
-- later-day office. Booked-out scenarios use Neuperlach and Riem (V48) so they
-- do not empty these rows while another scenario in the same browser job reads them.
--
-- English-only oeffnungszeit (see V45) has no Anzahlterminarbeitsplaetze. Skip a
-- column that is not present so Flyway still applies the other name.

SET @has_de := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'Anzahlterminarbeitsplaetze'
);
SET @has_en := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'appointment_workstation_count'
);

SET @de_foehring := IF(
  @has_de > 0,
  'UPDATE `oeffnungszeit` SET `Anzahlterminarbeitsplaetze` = 8 WHERE `OeffnungszeitID` = 136520',
  'SELECT 1'
);
PREPARE zmskvr1244_de_foehring FROM @de_foehring;
EXECUTE zmskvr1244_de_foehring;
DEALLOCATE PREPARE zmskvr1244_de_foehring;

SET @de_pasing := IF(
  @has_de > 0,
  'UPDATE `oeffnungszeit` SET `Anzahlterminarbeitsplaetze` = 6 WHERE `OeffnungszeitID` = 136521',
  'SELECT 1'
);
PREPARE zmskvr1244_de_pasing FROM @de_pasing;
EXECUTE zmskvr1244_de_pasing;
DEALLOCATE PREPARE zmskvr1244_de_pasing;

SET @en_foehring := IF(
  @has_en > 0,
  'UPDATE `oeffnungszeit` SET `appointment_workstation_count` = 8 WHERE `OeffnungszeitID` = 136520',
  'SELECT 1'
);
PREPARE zmskvr1244_en_foehring FROM @en_foehring;
EXECUTE zmskvr1244_en_foehring;
DEALLOCATE PREPARE zmskvr1244_en_foehring;

SET @en_pasing := IF(
  @has_en > 0,
  'UPDATE `oeffnungszeit` SET `appointment_workstation_count` = 6 WHERE `OeffnungszeitID` = 136521',
  'SELECT 1'
);
PREPARE zmskvr1244_en_pasing FROM @en_pasing;
EXECUTE zmskvr1244_en_pasing;
DEALLOCATE PREPARE zmskvr1244_en_pasing;
