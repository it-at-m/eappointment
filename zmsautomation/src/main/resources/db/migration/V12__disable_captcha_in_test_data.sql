-- Flyway migration: Disable captcha at all locations from test data because captcha isn't tested.

UPDATE standort 
SET captcha_activated_required = 0;