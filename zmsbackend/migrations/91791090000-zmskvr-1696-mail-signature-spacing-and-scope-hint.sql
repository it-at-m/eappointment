START TRANSACTION;

UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    'Landeshauptstadt München\\s*(<br\\s*/?>\\s*)+',
    'Landeshauptstadt München<br /><br /><br />\n'
)
WHERE `provider` = ''
  AND `name` = 'snippets.twig'
  AND `value` LIKE '%{\% block sendoff_munich \%}%';

UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    '(\\{% if process\\.scope\\.hint %\\}\\s*)<strong> \\{\\{ process\\.scope\\.hint \\}\\}',
    '\\1<br><strong> {{ process.scope.hint }}'
)
WHERE `provider` = ''
  AND `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
  );

COMMIT;
