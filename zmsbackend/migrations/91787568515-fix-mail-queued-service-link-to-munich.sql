START TRANSACTION;

-- Queued confirmation mails still linked to Berlin service pages:
--   https://service.berlin.de/dienstleistung/{{ request.id }}/standort/{{ process.scope.provider.id }}/
-- Other mail templates already use the München Dienstleistungsfinder:
--   http://www.muenchen.de/dienstleistungsfinder/muenchen/{{ ...root_parent_id }}/
--
-- Affects global and customized templates (same `name`, different `provider`).
-- Idempotent: only rows that still contain the Berlin dienstleistung URL are updated.

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    'https://service.berlin.de/dienstleistung/{{ request.id }}/standort/{{ process.scope.provider.id }}/',
    'http://www.muenchen.de/dienstleistungsfinder/muenchen/{{ request.root_parent_id }}/'
)
WHERE `value` LIKE '%https://service.berlin.de/dienstleistung/{{ request.id }}/standort/{{ process.scope.provider.id }}/%';

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    'http://service.berlin.de/dienstleistung/{{ request.id }}/standort/{{ process.scope.provider.id }}/',
    'http://www.muenchen.de/dienstleistungsfinder/muenchen/{{ request.root_parent_id }}/'
)
WHERE `value` LIKE '%http://service.berlin.de/dienstleistung/{{ request.id }}/standort/{{ process.scope.provider.id }}/%';

COMMIT;
