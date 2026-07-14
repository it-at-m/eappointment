ALTER TABLE standort
    ADD COLUMN `last_display_number` INT(5) DEFAULT 0,
    ADD COLUMN `max_display_number` INT(5) DEFAULT 9999,
    ADD COLUMN `display_number_prefix` VARCHAR(2) DEFAULT null;