-- Assign zmsautomation test users (V22__add_role_test_users.sql) to zmsdb roles.
-- Requires: role/user_role tables and nutzer rows from zmsautomation Flyway V22.

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
