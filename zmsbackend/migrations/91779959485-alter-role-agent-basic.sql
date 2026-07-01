DELETE rp
FROM role_permission rp
JOIN role r ON r.id = rp.role_id
WHERE r.name = 'agent_basic';

INSERT INTO role_permission (role_id, permission_id)
SELECT r.id, p.id
FROM role r
JOIN permission p
  ON p.name IN (
      'appointment',
      'customersearch',
      'emergency',
      'finishedqueue',
      'missedqueue',
      'parkedqueue'
  )
WHERE r.name = 'agent_basic';
