INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM role r
JOIN permission p ON p.name = 'jurisdiction'
WHERE r.name = 'system_admin';
