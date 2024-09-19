ALTER TABLE standort
    ADD COLUMN `delete_logs_older_than_days` INT(2) DEFAULT 90;