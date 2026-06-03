-- Flyway migration: Add workstation test users per role (login test_role_<role>, password vorschau)
-- role/user_role are not in .resources/zms.sql; bootstrap them here so Flyway can run before zmsapi migrate
-- Berechtigung 1 avoids extra user_role rows from migrate-users-to-new-roles.

CREATE TABLE IF NOT EXISTS role
(
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    description TEXT         NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_role_name (name)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

INSERT INTO role (name, description)
VALUES ('agent_basic', NULL),
       ('agent_queue', NULL),
       ('agent_queue_plus', NULL),
       ('appointment_admin', NULL),
       ('reporting_viewer', NULL),
       ('user_admin', NULL),
       ('audit_viewer', NULL),
       ('system_admin', NULL)
ON DUPLICATE KEY UPDATE description = VALUES(description);

CREATE TABLE IF NOT EXISTS user_role
(
    user_id INT(5) UNSIGNED NOT NULL,
    role_id INT UNSIGNED    NOT NULL,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_user_role_user
        FOREIGN KEY (user_id) REFERENCES nutzer (NutzerID),
    CONSTRAINT fk_user_role_role
        FOREIGN KEY (role_id) REFERENCES role (id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

INSERT IGNORE INTO `nutzer`
(`NutzerID`, `Name`, `Passworthash`, `Frage`, `Antworthash`, `Berechtigung`, `KundenID`, `BehoerdenID`, `SessionID`, `StandortID`, `Arbeitsplatznr`, `Datum`, `Kalenderansicht`, `clusteransicht`, `notrufinitiierung`, `notrufantwort`, `aufrufzusatz`, `lastUpdate`, `sessionExpiry`)
VALUES
  (5132, 'test_role_agent_basic', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5133, 'test_role_agent_queue', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5134, 'test_role_agent_queue_plus', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5135, 'test_role_appointment_admin', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5136, 'test_role_reporting_viewer', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5137, 'test_role_user_admin', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5138, 'test_role_audit_viewer', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 1, 0, 40, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL),
  (5139, 'test_role_system_admin', '$2y$10$9VlaB0aah3ypD5pXQCRyventPO5drQlOP.gqUk0BA5Iclfo2YTCoW', '', '', 90, 0, 0, '', 0, '', '0000-00-00', 0, 0, '0', '0', '', CURRENT_TIMESTAMP, NULL);

INSERT IGNORE INTO `nutzerzuordnung` (`nutzerid`, `behoerdenid`)
VALUES
  (5132, 40),
  (5133, 40),
  (5134, 40),
  (5135, 40),
  (5136, 40),
  (5138, 40),
  (5137, 0),
  (5139, 0);

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.NutzerID, r.id
FROM nutzer u
         JOIN role r ON r.name = 'agent_basic'
WHERE u.Name = 'test_role_agent_basic';

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.NutzerID, r.id
FROM nutzer u
         JOIN role r ON r.name = 'agent_queue'
WHERE u.Name = 'test_role_agent_queue';

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.NutzerID, r.id
FROM nutzer u
         JOIN role r ON r.name = 'agent_queue_plus'
WHERE u.Name = 'test_role_agent_queue_plus';

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.NutzerID, r.id
FROM nutzer u
         JOIN role r ON r.name = 'appointment_admin'
WHERE u.Name = 'test_role_appointment_admin';

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.NutzerID, r.id
FROM nutzer u
         JOIN role r ON r.name = 'reporting_viewer'
WHERE u.Name = 'test_role_reporting_viewer';

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.NutzerID, r.id
FROM nutzer u
         JOIN role r ON r.name = 'user_admin'
WHERE u.Name = 'test_role_user_admin';

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.NutzerID, r.id
FROM nutzer u
         JOIN role r ON r.name = 'audit_viewer'
WHERE u.Name = 'test_role_audit_viewer';

INSERT IGNORE INTO user_role (user_id, role_id)
SELECT u.NutzerID, r.id
FROM nutzer u
         JOIN role r ON r.name = 'system_admin'
WHERE u.Name = 'test_role_system_admin';
