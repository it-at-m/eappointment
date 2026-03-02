-- Flyway migration: Set email confirmation activated to false for scope 1 in ZMS-3171.feature Vorbelegung von "Mit E-Mail Bestätigung" ist konfigurierbar

UPDATE `scope` SET `email_confirmation_activated` = 0 WHERE `StandortID` = 1;