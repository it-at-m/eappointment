-- V28 inserted the pool under the old names (ataf_2, agent_queue_2, _system_messenger_2).
-- Rename those rows. The _1 accounts are new. Features still ask for ataf, agent_queue,
-- and _system_messenger; AccountCheckout hands out ataf_superuser_*, ataf_agent_queue_*,
-- and ataf__system_messenger_*.
-- One Benutzerverwaltung account per department is inserted below: ataf_user_admin_*.

UPDATE `nutzer`
SET `Name` = CASE `Name`
    WHEN 'ataf_2@keycloak' THEN 'ataf_superuser_2@keycloak'
    WHEN 'ataf_3@keycloak' THEN 'ataf_superuser_3@keycloak'
    WHEN 'ataf_4@keycloak' THEN 'ataf_superuser_4@keycloak'
    WHEN 'ataf_5@keycloak' THEN 'ataf_superuser_5@keycloak'
    WHEN 'ataf_6@keycloak' THEN 'ataf_superuser_6@keycloak'
    WHEN 'ataf_7@keycloak' THEN 'ataf_superuser_7@keycloak'
    WHEN 'ataf_8@keycloak' THEN 'ataf_superuser_8@keycloak'
    WHEN 'ataf_9@keycloak' THEN 'ataf_superuser_9@keycloak'
    WHEN 'ataf_10@keycloak' THEN 'ataf_superuser_10@keycloak'
    WHEN 'ataf_11@keycloak' THEN 'ataf_superuser_11@keycloak'
    WHEN 'ataf_12@keycloak' THEN 'ataf_superuser_12@keycloak'
    WHEN 'ataf_13@keycloak' THEN 'ataf_superuser_13@keycloak'
    WHEN 'ataf_14@keycloak' THEN 'ataf_superuser_14@keycloak'
    WHEN 'ataf_15@keycloak' THEN 'ataf_superuser_15@keycloak'
    WHEN 'ataf_16@keycloak' THEN 'ataf_superuser_16@keycloak'
    WHEN 'ataf_17@keycloak' THEN 'ataf_superuser_17@keycloak'
    WHEN 'agent_queue_2@keycloak' THEN 'ataf_agent_queue_2@keycloak'
    WHEN 'agent_queue_3@keycloak' THEN 'ataf_agent_queue_3@keycloak'
    WHEN 'agent_queue_4@keycloak' THEN 'ataf_agent_queue_4@keycloak'
    WHEN 'agent_queue_5@keycloak' THEN 'ataf_agent_queue_5@keycloak'
    WHEN 'agent_queue_6@keycloak' THEN 'ataf_agent_queue_6@keycloak'
    WHEN 'agent_queue_7@keycloak' THEN 'ataf_agent_queue_7@keycloak'
    WHEN 'agent_queue_8@keycloak' THEN 'ataf_agent_queue_8@keycloak'
    WHEN 'agent_queue_9@keycloak' THEN 'ataf_agent_queue_9@keycloak'
    WHEN 'agent_queue_10@keycloak' THEN 'ataf_agent_queue_10@keycloak'
    WHEN 'agent_queue_11@keycloak' THEN 'ataf_agent_queue_11@keycloak'
    WHEN 'agent_queue_12@keycloak' THEN 'ataf_agent_queue_12@keycloak'
    WHEN 'agent_queue_13@keycloak' THEN 'ataf_agent_queue_13@keycloak'
    WHEN 'agent_queue_14@keycloak' THEN 'ataf_agent_queue_14@keycloak'
    WHEN 'agent_queue_15@keycloak' THEN 'ataf_agent_queue_15@keycloak'
    WHEN 'agent_queue_16@keycloak' THEN 'ataf_agent_queue_16@keycloak'
    WHEN 'agent_queue_17@keycloak' THEN 'ataf_agent_queue_17@keycloak'
    WHEN 'agent_queue_18@keycloak' THEN 'ataf_agent_queue_18@keycloak'
    WHEN 'agent_queue_19@keycloak' THEN 'ataf_agent_queue_19@keycloak'
    WHEN 'agent_queue_20@keycloak' THEN 'ataf_agent_queue_20@keycloak'
    WHEN 'agent_queue_21@keycloak' THEN 'ataf_agent_queue_21@keycloak'
    WHEN 'agent_queue_22@keycloak' THEN 'ataf_agent_queue_22@keycloak'
    WHEN 'agent_queue_23@keycloak' THEN 'ataf_agent_queue_23@keycloak'
    WHEN 'agent_queue_24@keycloak' THEN 'ataf_agent_queue_24@keycloak'
    WHEN 'agent_queue_25@keycloak' THEN 'ataf_agent_queue_25@keycloak'
    WHEN 'agent_queue_26@keycloak' THEN 'ataf_agent_queue_26@keycloak'
    WHEN 'agent_queue_27@keycloak' THEN 'ataf_agent_queue_27@keycloak'
    WHEN 'agent_queue_28@keycloak' THEN 'ataf_agent_queue_28@keycloak'
    WHEN 'agent_queue_29@keycloak' THEN 'ataf_agent_queue_29@keycloak'
    WHEN 'agent_queue_30@keycloak' THEN 'ataf_agent_queue_30@keycloak'
    WHEN 'agent_queue_31@keycloak' THEN 'ataf_agent_queue_31@keycloak'
    WHEN 'agent_queue_32@keycloak' THEN 'ataf_agent_queue_32@keycloak'
    WHEN 'agent_queue_33@keycloak' THEN 'ataf_agent_queue_33@keycloak'
    WHEN '_system_messenger_2' THEN 'ataf__system_messenger_2'
    WHEN '_system_messenger_3' THEN 'ataf__system_messenger_3'
    WHEN '_system_messenger_4' THEN 'ataf__system_messenger_4'
    WHEN '_system_messenger_5' THEN 'ataf__system_messenger_5'
    WHEN '_system_messenger_6' THEN 'ataf__system_messenger_6'
    WHEN '_system_messenger_7' THEN 'ataf__system_messenger_7'
    WHEN '_system_messenger_8' THEN 'ataf__system_messenger_8'
    WHEN '_system_messenger_9' THEN 'ataf__system_messenger_9'
    WHEN '_system_messenger_10' THEN 'ataf__system_messenger_10'
    WHEN '_system_messenger_11' THEN 'ataf__system_messenger_11'
    WHEN '_system_messenger_12' THEN 'ataf__system_messenger_12'
    WHEN '_system_messenger_13' THEN 'ataf__system_messenger_13'
    WHEN '_system_messenger_14' THEN 'ataf__system_messenger_14'
    WHEN '_system_messenger_15' THEN 'ataf__system_messenger_15'
    WHEN '_system_messenger_16' THEN 'ataf__system_messenger_16'
    WHEN '_system_messenger_17' THEN 'ataf__system_messenger_17'
    WHEN '_system_messenger_18' THEN 'ataf__system_messenger_18'
    WHEN '_system_messenger_19' THEN 'ataf__system_messenger_19'
    WHEN '_system_messenger_20' THEN 'ataf__system_messenger_20'
    WHEN '_system_messenger_21' THEN 'ataf__system_messenger_21'
    WHEN '_system_messenger_22' THEN 'ataf__system_messenger_22'
    WHEN '_system_messenger_23' THEN 'ataf__system_messenger_23'
    WHEN '_system_messenger_24' THEN 'ataf__system_messenger_24'
    WHEN '_system_messenger_25' THEN 'ataf__system_messenger_25'
    WHEN '_system_messenger_26' THEN 'ataf__system_messenger_26'
    WHEN '_system_messenger_27' THEN 'ataf__system_messenger_27'
    WHEN '_system_messenger_28' THEN 'ataf__system_messenger_28'
    WHEN '_system_messenger_29' THEN 'ataf__system_messenger_29'
    WHEN '_system_messenger_30' THEN 'ataf__system_messenger_30'
    WHEN '_system_messenger_31' THEN 'ataf__system_messenger_31'
    WHEN '_system_messenger_32' THEN 'ataf__system_messenger_32'
    WHEN '_system_messenger_33' THEN 'ataf__system_messenger_33'
    ELSE `Name`
END
WHERE `Name` IN (
    'ataf_2@keycloak',
    'ataf_3@keycloak',
    'ataf_4@keycloak',
    'ataf_5@keycloak',
    'ataf_6@keycloak',
    'ataf_7@keycloak',
    'ataf_8@keycloak',
    'ataf_9@keycloak',
    'ataf_10@keycloak',
    'ataf_11@keycloak',
    'ataf_12@keycloak',
    'ataf_13@keycloak',
    'ataf_14@keycloak',
    'ataf_15@keycloak',
    'ataf_16@keycloak',
    'ataf_17@keycloak',
    'agent_queue_2@keycloak',
    'agent_queue_3@keycloak',
    'agent_queue_4@keycloak',
    'agent_queue_5@keycloak',
    'agent_queue_6@keycloak',
    'agent_queue_7@keycloak',
    'agent_queue_8@keycloak',
    'agent_queue_9@keycloak',
    'agent_queue_10@keycloak',
    'agent_queue_11@keycloak',
    'agent_queue_12@keycloak',
    'agent_queue_13@keycloak',
    'agent_queue_14@keycloak',
    'agent_queue_15@keycloak',
    'agent_queue_16@keycloak',
    'agent_queue_17@keycloak',
    'agent_queue_18@keycloak',
    'agent_queue_19@keycloak',
    'agent_queue_20@keycloak',
    'agent_queue_21@keycloak',
    'agent_queue_22@keycloak',
    'agent_queue_23@keycloak',
    'agent_queue_24@keycloak',
    'agent_queue_25@keycloak',
    'agent_queue_26@keycloak',
    'agent_queue_27@keycloak',
    'agent_queue_28@keycloak',
    'agent_queue_29@keycloak',
    'agent_queue_30@keycloak',
    'agent_queue_31@keycloak',
    'agent_queue_32@keycloak',
    'agent_queue_33@keycloak',
    '_system_messenger_2',
    '_system_messenger_3',
    '_system_messenger_4',
    '_system_messenger_5',
    '_system_messenger_6',
    '_system_messenger_7',
    '_system_messenger_8',
    '_system_messenger_9',
    '_system_messenger_10',
    '_system_messenger_11',
    '_system_messenger_12',
    '_system_messenger_13',
    '_system_messenger_14',
    '_system_messenger_15',
    '_system_messenger_16',
    '_system_messenger_17',
    '_system_messenger_18',
    '_system_messenger_19',
    '_system_messenger_20',
    '_system_messenger_21',
    '_system_messenger_22',
    '_system_messenger_23',
    '_system_messenger_24',
    '_system_messenger_25',
    '_system_messenger_26',
    '_system_messenger_27',
    '_system_messenger_28',
    '_system_messenger_29',
    '_system_messenger_30',
    '_system_messenger_31',
    '_system_messenger_32',
    '_system_messenger_33'
);

INSERT IGNORE INTO `nutzer`
(`NutzerID`, `Name`, `Passworthash`, `Frage`, `Antworthash`, `Berechtigung`, `KundenID`, `BehoerdenID`, `SessionID`, `StandortID`, `Arbeitsplatznr`, `Datum`, `Kalenderansicht`, `clusteransicht`, `notrufinitiierung`, `notrufantwort`, `aufrufzusatz`, `lastUpdate`, `sessionExpiry`)
VALUES
  (5220, 'ataf_superuser_1@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5221, 'ataf_agent_queue_1@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5222, 'ataf__system_messenger_1', '128196aca512b2989d1d442455a57629', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL);

INSERT IGNORE INTO `nutzerzuordnung` (`nutzerid`, `behoerdenid`)
VALUES
  (5220, 0),
  (5221, 40),
  (5222, 0);

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.NutzerID, r.id
FROM nutzer u
         JOIN role r ON r.name = 'agent_queue'
WHERE u.Name = 'ataf_agent_queue_1@keycloak';

-- One Benutzerverwaltung account per department that belongs to an organisation.
-- The base Testbehoerde has no organisation, so it is not in this pool and does
-- not add an ataf_user_admin_* login. Keycloak has the same logins.
-- The scenario takes a free one at random and the Behörde list is that account's department.
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
    SELECT b.BehoerdenID, ROW_NUMBER() OVER (ORDER BY b.BehoerdenID) AS n
    FROM behoerde b
             INNER JOIN organisation o ON o.OrganisationsID = b.OrganisationsID
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
