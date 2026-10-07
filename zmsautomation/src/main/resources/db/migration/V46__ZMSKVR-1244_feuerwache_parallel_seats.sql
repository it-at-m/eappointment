-- ZMSKVR-1244: more internet seats so chrome/firefox matrix jobs do not starve each other.
-- Föhring (136520) is used for Ort toggle / slot display. Pasing (136521) is used for
-- booked-out / mid-Termin reserve races; scenarios reserve every free seat there.

UPDATE `oeffnungszeit`
SET `Anzahlterminarbeitsplaetze` = 8
WHERE `OeffnungszeitID` = 136520;

UPDATE `oeffnungszeit`
SET `Anzahlterminarbeitsplaetze` = 6
WHERE `OeffnungszeitID` = 136521;

-- English column names when present (dual-schema DBs).
SET @has_en := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = 'oeffnungszeit'
    AND COLUMN_NAME = 'appointment_workstation_count'
);

SET @en_sql := IF(
  @has_en > 0,
  'UPDATE `oeffnungszeit` SET `appointment_workstation_count` = 8 WHERE `OeffnungszeitID` = 136520',
  'SELECT 1'
);
PREPARE zmskvr1244_en_foehring FROM @en_sql;
EXECUTE zmskvr1244_en_foehring;
DEALLOCATE PREPARE zmskvr1244_en_foehring;

SET @en_sql2 := IF(
  @has_en > 0,
  'UPDATE `oeffnungszeit` SET `appointment_workstation_count` = 6 WHERE `OeffnungszeitID` = 136521',
  'SELECT 1'
);
PREPARE zmskvr1244_en_pasing FROM @en_sql2;
EXECUTE zmskvr1244_en_pasing;
DEALLOCATE PREPARE zmskvr1244_en_pasing;
