DELETE FROM role_permission WHERE role_id = 1;

INSERT INTO role_permission (role_id, permission_id)
VALUES
    (1, 1),
    (1, 8),
    (1, 11),
    (1, 12),
    (1, 16),
    (1, 20);
