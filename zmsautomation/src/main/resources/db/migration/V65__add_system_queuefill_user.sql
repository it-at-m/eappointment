-- Local password user for the appointment fillers.
-- Login name is _system_queuefill (not @keycloak). Password is vorschau.
-- Berechtigung 90 matches superuser so the fillers can book every scope.
-- A separate row keeps the filler session off the superuser login.

INSERT IGNORE INTO `nutzer`
(`NutzerID`, `Name`, `Passworthash`, `Frage`, `Antworthash`, `Berechtigung`, `KundenID`, `BehoerdenID`, `SessionID`, `StandortID`, `Arbeitsplatznr`, `Datum`, `Kalenderansicht`, `clusteransicht`, `notrufinitiierung`, `notrufantwort`, `aufrufzusatz`, `lastUpdate`, `sessionExpiry`)
VALUES
  (5400, '_system_queuefill', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL);

INSERT IGNORE INTO `nutzerzuordnung` (`nutzerid`, `behoerdenid`)
VALUES
  (5400, 0);
