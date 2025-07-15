TRUNCATE TABLE `gesamtkalender`;

ALTER TABLE `gesamtkalender`
    MODIFY `availability_id` INT UNSIGNED NULL;

ALTER TABLE `gesamtkalender`
    ADD INDEX `idx_scope_time_seat` (`scope_id`, `time`, `seat`);

ALTER TABLE `gesamtkalender`
DROP INDEX `uk_scope_time`;

ALTER TABLE `gesamtkalender`
    ADD UNIQUE KEY `uk_scope_time` (`scope_id`, `time`, `seat`);

ALTER TABLE `gesamtkalender`
DROP INDEX `idx_scope_time_seat`;