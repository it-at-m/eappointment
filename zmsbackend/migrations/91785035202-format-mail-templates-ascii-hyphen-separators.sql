START TRANSACTION;

-- ZMSKVR-352 follow-up: zms-dev (and any env that already applied
-- 91785035201) still showed ASCII-hyphen separators (---) and the old
-- <br><br> spacing. The first migration only matched double em-dashes (——).
--
-- Same transforms as the updated 91785035201; idempotent so later
-- environments that run both files are unchanged the second time.

-- 1) Leistungen: <strong> um Service-Links entfernen (beliebige href)
UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    '<strong>[[:space:]]*<a href="([^"]+)">[[:space:]]*(\\{\\{ requestGroup\\[''request''\\]\\.name \\}\\})[[:space:]]*</a>[[:space:]]*</strong>',
    '<a href="\\1">\\2 </a>'
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` REGEXP '<strong>[[:space:]]*<a href="[^"]+">[[:space:]]*\\{\\{ requestGroup\\[''request''\\]\\.name';

-- 2) Trennlinie aus genau drei ASCII-Bindestrichen → Geviertstrich
UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    CONCAT(
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')',
        '([[:blank:]]*)---([[:blank:]]*)',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')'
    ),
    CONCAT('\\1', '\\2', '—', '\\3', '\\4')
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` REGEXP CONCAT(
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')',
        '[[:blank:]]*---[[:blank:]]*',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')'
    );

-- 3) Trennlinie aus doppeltem Geviertstrich → einzelner Geviertstrich
UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    CONCAT(
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')',
        '([[:blank:]]*)——([[:blank:]]*)',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')'
    ),
    CONCAT('\\1', '\\2', '—', '\\3', '\\4')
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%——%';

-- 4) <br><br> / extra Leerzeilen um den Geviertstrich → je ein <br>
UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    CONCAT(
        '<br[[:space:]]*/?>',
        '([[:space:]]*<br[[:space:]]*/?>)?',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')+',
        '([[:blank:]]*)—([[:blank:]]*)',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')+',
        '([[:blank:]]*)<br[[:space:]]*/?>',
        '([[:space:]]*<br[[:space:]]*/?>)?'
    ),
    CONCAT('<br>', CHAR(13), CHAR(10), '\\3—\\4', CHAR(13), CHAR(10), '\\6<br>')
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%—%'
  AND (
        `value` LIKE '%<br><br>%—%'
     OR `value` LIKE '%—%<br><br>%'
     OR `value` LIKE '%<br /><br />%—%'
  );

-- 5) Abstand unter "Termin aktivieren" reduzieren
UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    'Termin aktivieren</a></strong>[[:space:]]*<br[[:space:]]*/?>([[:space:]]*<br[[:space:]]*/?>)?',
    CONCAT('Termin aktivieren</a></strong>', CHAR(13), CHAR(10), '<br>')
)
WHERE `name` = 'mail_preconfirmed.twig'
  AND `value` LIKE '%Termin aktivieren%</strong>%<br%';

-- 6) Catch-all: verbleibender doppelter Geviertstrich
UPDATE `mailtemplate`
SET `value` = REPLACE(`value`, '——', '—')
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%——%';

COMMIT;
