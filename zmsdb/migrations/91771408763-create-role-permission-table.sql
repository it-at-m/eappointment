CREATE TABLE IF NOT EXISTS role_permission
(
    role_id       INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,

    PRIMARY KEY (role_id, permission_id),

    CONSTRAINT fk_role_permission_role
        FOREIGN KEY (role_id) REFERENCES role (id),

    CONSTRAINT fk_role_permission_permission
        FOREIGN KEY (permission_id) REFERENCES permission (id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM role r
         JOIN permission p ON p.name IN ('appointment', 'emergency', 'counter', 'customersearch')
WHERE r.name = 'agent_basic';

INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM role r
         JOIN permission p ON p.name IN (
                                         'appointment', 'emergency', 'counter', 'customersearch', 'cherrypick',
                                         'waitingqueue', 'parkedqueue', 'missedqueue', 'openqueue', 'finishedqueue'
    )
WHERE r.name = 'agent_queue';

INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM role r
         JOIN permission p ON p.name IN (
                                         'appointment', 'emergency', 'counter', 'customersearch', 'cherrypick',
                                         'waitingqueue', 'parkedqueue', 'missedqueue', 'openqueue', 'finishedqueue',
                                         'overallcalendar'
    )
WHERE r.name = 'agent_queue_plus';

INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM role r
         JOIN permission p ON p.name IN (
                                         'appointment', 'emergency', 'counter', 'customersearch', 'cherrypick',
                                         'waitingqueue', 'parkedqueue', 'missedqueue', 'openqueue', 'finishedqueue',
                                         'overallcalendar', 'restrictedscope', 'statistic'
    )
WHERE r.name = 'appointment_admin';

INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM role r
         JOIN permission p ON p.name IN ('statistic')
WHERE r.name = 'reporting_viewer';

INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM role r
         JOIN permission p ON p.name IN ('useraccount')
WHERE r.name = 'user_admin';

INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM role r
         JOIN permission p ON p.name IN ('customersearch', 'logs')
WHERE r.name = 'audit_viewer';

INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM role r
         JOIN permission p ON p.name IN ('superuser')
WHERE r.name = 'system_admin';
