INSERT INTO permission (name, description)
VALUES ('jurisdiction', 'Kunde (Auftraggeber) erstellen/aktualisieren/löschen')
ON DUPLICATE KEY UPDATE description = VALUES(description);
