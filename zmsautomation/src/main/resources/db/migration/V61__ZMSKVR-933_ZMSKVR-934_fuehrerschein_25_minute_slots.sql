-- ZMSKVR-933 / ZMSKVR-934: placeholder.
-- Hourly DLDB reimports providers and rewrites availability.slotTimeInMinutes from
-- provider.data, so a Flyway pin here would not survive. The ATAF pin runs after
-- hourly in zmsautomation-test via db/ataf-after-dldb.sql (before minutly).
SELECT 1;
