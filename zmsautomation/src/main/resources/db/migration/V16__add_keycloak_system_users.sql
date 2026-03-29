-- Flyway migration: Add Keycloak-backed test/system users (local UI + citizen api tests)
-- Password for all users is "vorschau" (same bcrypt hash reused).
--
-- Matches Keycloak migration:
--   .resources/keycloak/migration/07_add-system-users.yml
--
-- Note: ZMS loginname stored as "<keycloak-username>@keycloak" after SSO.

INSERT INTO `nutzer`
(`NutzerID`, `Name`, `Passworthash`, `Frage`, `Antworthash`, `Berechtigung`, `KundenID`, `BehoerdenID`, `SessionID`, `StandortID`, `Arbeitsplatznr`, `Datum`, `Kalenderansicht`, `clusteransicht`, `notrufinitiierung`, `notrufantwort`, `aufrufzusatz`, `lastUpdate`, `sessionExpiry`)
VALUES
  (5127, 'ataf@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5128, '_system_115@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5129, '_system_citizenapi@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5130, '_system_messenger@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5131, '_system_soap@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL);

