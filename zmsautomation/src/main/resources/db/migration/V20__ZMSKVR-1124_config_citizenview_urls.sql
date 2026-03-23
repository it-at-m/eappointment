-- Flyway migration: Point appointment URLs to the active citizenview base URL
-- for the current test environment to fetch activation and confirmation links
--
-- The actual citizenview base URL is injected via the Flyway placeholder
-- ${citizenviewBaseUrl}. It is environment-specific:
-- - local/devcontainer typically uses http://citizenview:8082/
-- - CI currently uses http://citizenview:8080/
--
-- The test setup passes that value from CITIZENVIEW_BASE_URI when running
-- flyway:migrate, so the actual SQL stays in this migration.

UPDATE `config`
SET `value` = '${citizenviewBaseUrl}',
    `changeTimestamp` = NOW()
WHERE `name` = 'appointments__urlAppointments';

UPDATE `config`
SET `value` = '${citizenviewBaseUrl}',
    `changeTimestamp` = NOW()
WHERE `name` = 'appointments__urlChange';
