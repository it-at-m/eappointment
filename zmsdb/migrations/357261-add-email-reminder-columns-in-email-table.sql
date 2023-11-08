ALTER TABLE `email`
    ADD COLUMN `send_reminder` TinyInt(1) DEFAULT 1,
    ADD COLUMN `send_reminder_minutes_before` INT(5) NULL;