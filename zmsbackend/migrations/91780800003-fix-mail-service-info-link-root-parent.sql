START TRANSACTION;

-- Fix service info links for nested variants in stored mail templates.
--
-- München templates used the internal request row id:
--   {{ requestGroup['request'].id }}
-- which breaks for nested variants (e.g. Telefon → base variant → root service).
--
-- root_parent_id is resolved on Request entities when loaded from zmsdb.
-- This migration normalises all known legacy Twig expressions to root_parent_id.

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    '{% set requestId = requestGroup[\'request\'].id %}',
    '{% set requestId = requestGroup[\'request\'].root_parent_id %}'
)
WHERE `value` LIKE '%requestGroup[\'request\'].id %}%';

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    '{% set requestId = requestGroup[\'request\'].serviceInfoId %}',
    '{% set requestId = requestGroup[\'request\'].root_parent_id %}'
)
WHERE `value` LIKE '%requestGroup[\'request\'].serviceInfoId %}%';

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    '{{ requestGroup[\'request\'].id }}',
    '{{ requestGroup[\'request\'].root_parent_id }}'
)
WHERE `value` LIKE '%{{ requestGroup[\'request\'].id }}%';

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    '{{ requestGroup[\'request\'].serviceInfoId }}',
    '{{ requestGroup[\'request\'].root_parent_id }}'
)
WHERE `value` LIKE '%{{ requestGroup[\'request\'].serviceInfoId }}%';

COMMIT;
