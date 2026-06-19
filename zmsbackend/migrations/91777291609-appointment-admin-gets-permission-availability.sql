INSERT IGNORE INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM role r
JOIN permission p
WHERE r.name = 'appointment_admin'
  AND p.name = 'availability';