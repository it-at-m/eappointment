INSERT INTO permission (name, description)
VALUES ('capacityreport', 'Terminkapazität abrufen/exportieren')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM role r
         JOIN permission p ON p.name = 'capacityreport'
WHERE r.name = 'system_admin';
