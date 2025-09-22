UPDATE mailtemplate
SET value = REPLACE(
        REPLACE(value, ''queue.number'', ''displayNumber''),
        ''process.id'', ''process.displayNumber''
    )
WHERE value LIKE ''%queue.number%'' OR value LIKE ''%process.id%'';