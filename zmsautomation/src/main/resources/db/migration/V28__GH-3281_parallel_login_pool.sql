-- Spare logins so a parallel ATAF run does not share one SessionID.
-- Password is "vorschau". OIDC names use the bcrypt hash from V16.
-- Mail login posts the raw id, so messenger spares match nutzer 5118 (_system_messenger),
-- whose hash is the one shipped in .resources/zms.sql.
--
-- Pools (including the existing user):
--   superuser  ataf, ataf_2, ataf_3          UI threads 2 + 1
--   workstation agent_queue .. agent_queue_5 API threads 4 + 1
--   messenger  _system_messenger .. _5       API threads 4 + 1
--
-- Queue and customer-call type a counter. The spare superusers sit on a different
-- counter: the feature number plus 100 times the pool index (ataf_2 -> +100, ataf_3 -> +200).
-- Arbeitsplatznr records that home desk.

INSERT IGNORE INTO `nutzer`
(`NutzerID`, `Name`, `Passworthash`, `Frage`, `Antworthash`, `Berechtigung`, `KundenID`, `BehoerdenID`, `SessionID`, `StandortID`, `Arbeitsplatznr`, `Datum`, `Kalenderansicht`, `clusteransicht`, `notrufinitiierung`, `notrufantwort`, `aufrufzusatz`, `lastUpdate`, `sessionExpiry`)
VALUES
  (5140, 'ataf_2@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 90, 0, 0, '', 0, '101', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5141, 'ataf_3@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 90, 0, 0, '', 0, '102', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5142, 'agent_queue_2@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5143, 'agent_queue_3@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5144, 'agent_queue_4@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5145, 'agent_queue_5@keycloak', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5146, '_system_messenger_2', '128196aca512b2989d1d442455a57629', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5147, '_system_messenger_3', '128196aca512b2989d1d442455a57629', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5148, '_system_messenger_4', '128196aca512b2989d1d442455a57629', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5149, '_system_messenger_5', '128196aca512b2989d1d442455a57629', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL);

INSERT IGNORE INTO `nutzerzuordnung` (`nutzerid`, `behoerdenid`)
VALUES
  (5140, 0),
  (5141, 0),
  (5142, 40),
  (5143, 40),
  (5144, 40),
  (5145, 40),
  (5146, 0),
  (5147, 0),
  (5148, 0),
  (5149, 0);

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.NutzerID, r.id
FROM nutzer u
         JOIN role r ON r.name = 'agent_queue'
WHERE u.Name IN (
    'agent_queue_2@keycloak',
    'agent_queue_3@keycloak',
    'agent_queue_4@keycloak',
    'agent_queue_5@keycloak'
);
