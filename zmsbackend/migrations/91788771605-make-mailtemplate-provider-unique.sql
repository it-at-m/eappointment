UPDATE mailtemplate
SET provider = ''
WHERE provider IS NULL;

DELETE older
FROM mailtemplate older
INNER JOIN mailtemplate newer
    ON older.name = newer.name
    AND older.provider = newer.provider
    AND (
        older.changeTimestamp < newer.changeTimestamp
        OR (
            older.changeTimestamp = newer.changeTimestamp
            AND older.id < newer.id
        )
    );

ALTER TABLE mailtemplate
    MODIFY provider VARCHAR(250) NOT NULL DEFAULT '',
    DROP INDEX index_name_and_provider,
    ADD UNIQUE KEY uniq_mailtemplate_name_provider (name, provider);
