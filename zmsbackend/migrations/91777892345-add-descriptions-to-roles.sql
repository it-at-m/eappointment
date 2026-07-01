UPDATE role SET description = 'Sachbearbeitung (Basis)'
WHERE name = 'agent_basic';

UPDATE role SET description = 'Sachbearbeitung (Standard)'
WHERE name = 'agent_queue';

UPDATE role SET description = 'Sachbearbeitung (Erweitert)'
WHERE name = 'agent_queue_plus';

UPDATE role SET description = 'Terminadministration'
WHERE name = 'appointment_admin';

UPDATE role SET description = 'Controlling'
WHERE name = 'reporting_viewer';

UPDATE role SET description = 'Benutzerverwaltung'
WHERE name = 'user_admin';

UPDATE role SET description = 'Innenrevision'
WHERE name = 'audit_viewer';

UPDATE role SET description = 'Technische Administration'
WHERE name = 'system_admin';