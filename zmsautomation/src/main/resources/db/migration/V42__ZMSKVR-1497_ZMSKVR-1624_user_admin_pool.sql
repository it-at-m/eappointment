-- Benutzerverwaltung pool. A feature that says user_admin takes a free member
-- that already has a department and waits if every such member is in use.
-- Sixteen UI browsers plus one spare. The single user_admin@keycloak row stays
-- without a department and is not part of the pool.
-- Password is vorschau. Department 40 is the department the other role accounts use.

INSERT IGNORE INTO `nutzer`
(`NutzerID`, `Name`, `Passworthash`, `Frage`, `Antworthash`, `Berechtigung`, `KundenID`, `BehoerdenID`, `SessionID`, `StandortID`, `Arbeitsplatznr`, `Datum`, `Kalenderansicht`, `clusteransicht`, `notrufinitiierung`, `notrufantwort`, `aufrufzusatz`, `lastUpdate`, `sessionExpiry`)
VALUES
  (5230, 'ataf_user_admin_1@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5231, 'ataf_user_admin_2@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5232, 'ataf_user_admin_3@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5233, 'ataf_user_admin_4@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5234, 'ataf_user_admin_5@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5235, 'ataf_user_admin_6@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5236, 'ataf_user_admin_7@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5237, 'ataf_user_admin_8@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5238, 'ataf_user_admin_9@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5239, 'ataf_user_admin_10@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5240, 'ataf_user_admin_11@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5241, 'ataf_user_admin_12@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5242, 'ataf_user_admin_13@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5243, 'ataf_user_admin_14@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5244, 'ataf_user_admin_15@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5245, 'ataf_user_admin_16@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5246, 'ataf_user_admin_17@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL);

INSERT IGNORE INTO `nutzerzuordnung` (`nutzerid`, `behoerdenid`)
VALUES
  (5230, 40),
  (5231, 40),
  (5232, 40),
  (5233, 40),
  (5234, 40),
  (5235, 40),
  (5236, 40),
  (5237, 40),
  (5238, 40),
  (5239, 40),
  (5240, 40),
  (5241, 40),
  (5242, 40),
  (5243, 40),
  (5244, 40),
  (5245, 40),
  (5246, 40);

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.NutzerID, r.id
FROM nutzer u
         JOIN role r ON r.name = 'user_admin'
WHERE u.Name LIKE 'ataf_user_admin_%@keycloak';
