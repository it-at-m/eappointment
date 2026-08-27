START TRANSACTION;

-- ZMSKVR-1350: Remove erroneous comma after postal code (PLZ) in address lines
-- of appointment confirmation and reminder mails.
--
-- German address format is "PLZ Ort" (e.g. "81667 München"), not "PLZ, Ort".
-- Affects global and customized templates (same `name`, different `provider`).
--
-- Example before:
--   {{process.scope.provider.contact.postalCode}}, {{process.scope.provider.contact.city}}
-- Example after:
--   {{process.scope.provider.contact.postalCode}} {{process.scope.provider.contact.city}}

UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    '(\\{\\{[[:space:]]*process\\.scope\\.provider\\.contact\\.postalCode[[:space:]]*\\}\\})[[:space:]]*,[[:space:]]*(\\{\\{[[:space:]]*process\\.scope\\.provider\\.contact\\.city[[:space:]]*\\}\\})',
    '\\1 \\2'
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` REGEXP 'postalCode[[:space:]]*\\}\\}[[:space:]]*,[[:space:]]*\\{\\{[[:space:]]*process\\.scope\\.provider\\.contact\\.city';

COMMIT;
