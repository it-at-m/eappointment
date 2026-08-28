UPDATE permission
SET description = 'Warteschlange sehen'
WHERE name = 'waitingqueue';

UPDATE permission
SET description = 'Alle Funktionen'
WHERE name = 'superuser';

UPDATE permission
SET description = 'Termine direkt aus der Warteschlange aufrufen (hierfür wird queue benötigt)'
WHERE name = 'cherrypick';
