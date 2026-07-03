START TRANSACTION;

-- Fix service info link for nested variants in mail templates.
-- Previously the link used the internal request row id ({{ requestGroup['request'].id }}),
-- which for variants points to the immediate parent/variant row instead of the
-- root service. request.data.id holds the root service id (e.g. 1080455) for
-- dldb requests, so use it with a fallback to the original id.
UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    '{{ requestGroup[\'request\'].id }}',
    '{{ requestGroup[\'request\'].data.id|default(requestGroup[\'request\'].id) }}'
)
WHERE `value` LIKE '%{{ requestGroup[\'request\'].id }}%';

COMMIT;
