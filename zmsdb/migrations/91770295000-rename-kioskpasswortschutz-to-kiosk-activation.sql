-- Rename organisation column to reflect actual behaviour (kiosks must be explicitly activated)
ALTER TABLE `organisation`
  CHANGE COLUMN `kioskpasswortschutz` `kiosk_activation` INT(2) DEFAULT 0;
