START TRANSACTION;

-- ZMSKVR-1696 follow-up to 91791090000: that migration only matched
-- `<strong> {{ process.scope.hint }}`, so mail_reminder.twig, which still carries
-- the `<strong>Wartezone: {{ process.scope.hint }}` markup, was left with the hint
-- on the same line as the Terminnummer.
-- Rows that already have `<br>` before `<strong>` no longer match, they got fixed during first migration.

UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    '(\\{% if process\\.scope\\.hint %\\}\\s*)<strong>',
    '\\1<br><strong>'
)
WHERE `provider` = ''
  AND `name` = 'mail_reminder.twig';

COMMIT;
