INSERT INTO `config` (`name`, `value`)
VALUES
    ('processSearchHistory__deleteOlderThanDays', '90'),
    ('cron__deleteOldProcessSearchHistory', 'prod,stage,dev');