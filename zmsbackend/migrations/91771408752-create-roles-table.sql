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

