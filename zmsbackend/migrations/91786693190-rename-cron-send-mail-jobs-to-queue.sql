-- Rename cron config keys so they match the queue-writer jobs (SMTP send stays in zmsmessaging).
UPDATE `config` SET `name` = 'cron__queueMailReminder' WHERE `name` = 'cron__sendMailReminder';
UPDATE `config` SET `name` = 'cron__queueProcessListToScopeAdmin' WHERE `name` = 'cron__sendProcessListToScopeAdmin';

-- Job was removed; drop the leftover config key.
DELETE FROM `config` WHERE `name` = 'cron__sendNotificationReminder';
