-- One Benutzerverwaltung account per department. The scenario takes a free one
-- at random and the Behörde list is that account's department.
-- ZMS role user_admin. Berechtigung stays 1.
-- Technische Administration is system_admin. Terminadministration is appointment_admin.
-- Keycloak grants only the client role user. This migration assigns user_admin.
-- Logins are ataf_user_admin_1 onward, numbered in BehoerdenID order, starting at NutzerID 5230.
-- The single user_admin@keycloak row stays without a department and is not in the pool.
-- Password is vorschau.

INSERT IGNORE INTO `nutzer`
(`NutzerID`, `Name`, `Passworthash`, `Frage`, `Antworthash`, `Berechtigung`, `KundenID`, `BehoerdenID`, `SessionID`, `StandortID`, `Arbeitsplatznr`, `Datum`, `Kalenderansicht`, `clusteransicht`, `notrufinitiierung`, `notrufantwort`, `aufrufzusatz`, `lastUpdate`, `sessionExpiry`)
SELECT
    5229 + departments.n,
    CONCAT('ataf_user_admin_', departments.n, '@keycloak'),
    '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW',
    '',
    '',
    1,
    0,
    departments.BehoerdenID,
    '',
    0,
    '',
    '0000-00-00',
    0,
    0,
    '0',
    '0',
    '',
    CURRENT_TIMESTAMP,
    NULL
FROM (
    SELECT BehoerdenID, ROW_NUMBER() OVER (ORDER BY BehoerdenID) AS n
    FROM behoerde
) departments;

INSERT IGNORE INTO `nutzerzuordnung` (`nutzerid`, `behoerdenid`)
SELECT NutzerID, BehoerdenID
FROM nutzer
WHERE Name LIKE 'ataf_user_admin_%@keycloak'
  AND BehoerdenID <> 0;

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.NutzerID, r.id
FROM nutzer u
         JOIN role r ON r.name = 'user_admin'
WHERE u.Name LIKE 'ataf_user_admin_%@keycloak';
