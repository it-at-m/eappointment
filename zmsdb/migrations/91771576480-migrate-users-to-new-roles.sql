INSERT
IGNORE
INTO user_role (user_id, role_id)
SELECT n.NutzerID AS user_id,
       r.id       AS role_id
FROM nutzer n
         JOIN role r
              ON r.name = CASE n.Berechtigung
                              WHEN 90 THEN 'system_admin'
                              WHEN 40 THEN 'user_admin' -- Fachliche Administration -> Benutzerverwaltung
                              WHEN 30 THEN 'appointment_admin'
                              WHEN 5 THEN 'audit_viewer'
                              WHEN 0 THEN 'agent_queue' -- Sachbearbeitung -> agent_queue
                              ELSE NULL
                  END
WHERE n.Berechtigung IN (0, 5, 30, 40, 90);