-- Flyway migration: Point appointment URLs to citizenview container (ZMSKVR-1124)
--
-- For local/devcontainer runs, mail links (e.g. preconfirmation "Termin bestätigen")
-- must point at the citizenview container host and port so the browser can open them.
-- Replaces https://service.berlin.de/terminvereinbarung/ with http://citizenview:8082/

UPDATE `config`
SET `value` = 'http://citizenview:8082/',
    `changeTimestamp` = NOW()
WHERE `name` = 'appointments__urlAppointments';

UPDATE `config`
SET `value` = 'http://citizenview:8082/',
    `changeTimestamp` = NOW()
WHERE `name` = 'appointments__urlChange';
