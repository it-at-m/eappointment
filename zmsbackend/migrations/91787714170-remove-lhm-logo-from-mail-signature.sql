START TRANSACTION;

-- ZMSKVR-1417: Remove LHM logo from Termin-E-Mail signatures.
-- Presse- und Informationsamt: the LHM logo must not be used in e-mails.
-- Lives in snippets.twig block sendoff_munich (global and customized copies).

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT(
        '<img src="https://assets.muenchen.de/logos/lhm/logo-lhm-muenchen-256.jpg"',
        ' alt="Logo der Landeshauptstadt München" width="180" height="auto">'
    ),
    ''
)
WHERE `value` LIKE '%logo-lhm-muenchen-256.jpg%';

UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    '<img[^>]*src="https://assets\\.muenchen\\.de/logos/lhm/[^"]*"[^>]*>',
    ''
)
WHERE `value` LIKE '%assets.muenchen.de/logos/lhm/%';

COMMIT;
