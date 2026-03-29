-- Flyway migration: Enable Keycloak login button
-- Set oidc__provider to 'keycloak' to enable the Keycloak SSO login button
-- in zmsadmin and zmsstatistic applications

UPDATE `config` SET `value` = 'keycloak' WHERE `name` = 'oidc__provider';
