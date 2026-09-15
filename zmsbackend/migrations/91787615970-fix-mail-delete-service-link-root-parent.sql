START TRANSACTION;

-- ZMSKVR-1597: Absage mails still linked Dienstleistung pages via request.id.
-- Variants have no DLDB/stadt.muenchen.de page at that id (empty or 404).
-- Same replacement as 91780800003, scoped to cancellation templates.

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    '{{ requestGroup[\'request\'].id }}',
    '{{ requestGroup[\'request\'].root_parent_id }}'
)
WHERE `name` IN ('mail_delete.twig', 'mail_admin_delete.twig')
  AND `value` LIKE '%{{ requestGroup[\'request\'].id }}%';

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    '{% set requestId = requestGroup[\'request\'].id %}',
    '{% set requestId = requestGroup[\'request\'].root_parent_id %}'
)
WHERE `name` IN ('mail_delete.twig', 'mail_admin_delete.twig')
  AND `value` LIKE '%requestGroup[\'request\'].id %}%';

COMMIT;
