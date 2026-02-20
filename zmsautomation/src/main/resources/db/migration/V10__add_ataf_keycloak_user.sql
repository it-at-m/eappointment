-- Flyway migration: Add ATAF test user for Keycloak SSO (local UI tests)
-- Matches the ataf user created in Keycloak migration 07_add-ataf-user.yml (ataf@keycloak after login)

INSERT INTO `nutzer` (`NutzerID`, `Name`, `Passworthash`, `Frage`, `Antworthash`, `Berechtigung`, `KundenID`, `BehoerdenID`, `SessionID`, `StandortID`, `Arbeitsplatznr`, `Datum`, `Kalenderansicht`, `clusteransicht`, `notrufinitiierung`, `notrufantwort`, `aufrufzusatz`, `lastUpdate`, `sessionExpiry`) VALUES
(5127, 'ataf@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL);
