-- Flyway migration: Update Kundenname to München
-- Change the customer name from 'Teststadt' to 'München' for Munich test data

UPDATE `kunde` SET `Kundenname` = 'München', `Anschrift` = 'München' WHERE `KundenID` = 1;
