-- Add initial descriptions for core roles

UPDATE role
SET description = 'Standardrolle für Sachbearbeitung im Fachbereich.'
WHERE name = 'agent_basic';

UPDATE role
SET description = 'Sachbearbeitung mit Warteschlangenverwaltung im Tresenbetrieb.'
WHERE name = 'agent_queue';

UPDATE role
SET description = 'Erweiterte Sachbearbeitung mit zusätzlichen Berechtigungen.'
WHERE name = 'agent_queue_plus';

UPDATE role
SET description = 'Terminadministration für Standorte und Zeitfenster.'
WHERE name = 'appointment_admin';

UPDATE role
SET description = 'Lesender Zugriff für Innenrevision und Protokolle.'
WHERE name = 'audit_viewer';

UPDATE role
SET description = 'Lesender Zugriff auf Statistiken und Auswertungen.'
WHERE name = 'reporting_viewer';

UPDATE role
SET description = 'Technische Systemadministration mit allen Rechten.'
WHERE name = 'system_admin';

UPDATE role
SET description = 'Fachliche Administration von Nutzerkonten und Berechtigungen.'
WHERE name = 'user_admin';

