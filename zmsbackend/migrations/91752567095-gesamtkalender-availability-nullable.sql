TRUNCATE TABLE `gesamtkalender`;

ALTER TABLE `gesamtkalender`
    MODIFY `availability_id` INT UNSIGNED NULL,
    ADD INDEX IF NOT EXISTS idx_scope_time_seat (`scope_id`, `time`, `seat`);

ALTER TABLE `gesamtkalender`
DROP INDEX IF EXISTS uk_scope_time;

ALTER TABLE `gesamtkalender`
    ADD UNIQUE INDEX IF NOT EXISTS uk_scope_time (`scope_id`, `time`, `seat`);

ALTER TABLE `gesamtkalender`
DROP INDEX IF EXISTS idx_scope_time_seat;
