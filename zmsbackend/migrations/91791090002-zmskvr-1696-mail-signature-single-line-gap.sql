START TRANSACTION;

-- ZMSKVR-1696 follow-up to 91791090000

UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    'Landeshauptstadt München\\s*(<br\\s*/?>\\s*)+',
    'Landeshauptstadt München<br /><br />\n'
)
WHERE `provider` = ''
  AND `name` = 'snippets.twig'
  AND `value` LIKE '%{\% block sendoff_munich \%}%';

COMMIT;
