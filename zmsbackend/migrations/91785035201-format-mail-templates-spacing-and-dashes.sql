START TRANSACTION;

-- ZMSKVR-352: Formatierung der Standard-E-Mail-Templates
--
-- - Leistungen (Service-Links) nicht mehr bold
-- - Trennstriche einheitlich als einzelner Geviertstrich (—) statt ——
-- - Abstände um Trennstriche / unter "Termin aktivieren" / unter Ort / unter Bullet-Liste reduzieren
--
-- Betrifft mail_confirmation, mail_preconfirmed und mail_reminder (global + customized).

-- 1) Leistungen: remove <strong> around service info links
UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    '<strong><a href="https://stadt.muenchen.de/service/info/{{ requestGroup[''request''].id }}/">{{ requestGroup[''request''].name }} </a></strong>',
    '<a href="https://stadt.muenchen.de/service/info/{{ requestGroup[''request''].id }}/">{{ requestGroup[''request''].name }} </a>'
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%<strong><a href="https://stadt.muenchen.de/service/info/%';

-- Also cover templates that already use root_parent_id / requestId in the href
UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    '<strong><a href="https://stadt.muenchen.de/service/info/{{ requestId }}/">{{ requestGroup[''request''].name }} </a></strong>',
    '<a href="https://stadt.muenchen.de/service/info/{{ requestId }}/">{{ requestGroup[''request''].name }} </a>'
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%<strong><a href="https://stadt.muenchen.de/service/info/{{ requestId }}/">%';

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    '<strong><a href="https://stadt.muenchen.de/service/info/{{ requestGroup[''request''].root_parent_id }}/">{{ requestGroup[''request''].name }} </a></strong>',
    '<a href="https://stadt.muenchen.de/service/info/{{ requestGroup[''request''].root_parent_id }}/">{{ requestGroup[''request''].name }} </a>'
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%<strong><a href="https://stadt.muenchen.de/service/info/{{ requestGroup[''request''].root_parent_id }}/">%';

-- 2) Aktivierung: tighten space below "Termin aktivieren" before separator (CRLF)
UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT(
        '<strong><a href="{{ confirmLink }}" target="_blank">Termin aktivieren</a></strong>  <br /><br />',
        CHAR(13), CHAR(10), CHAR(13), CHAR(10),
        '<br>', CHAR(13), CHAR(10),
        '——', CHAR(13), CHAR(10),
        '<br><br>'
    ),
    CONCAT(
        '<strong><a href="{{ confirmLink }}" target="_blank">Termin aktivieren</a></strong>',
        CHAR(13), CHAR(10),
        '<br>', CHAR(13), CHAR(10),
        '—', CHAR(13), CHAR(10),
        '<br>'
    )
)
WHERE `name` = 'mail_preconfirmed.twig'
  AND `value` LIKE '%Termin aktivieren%</strong>  <br /><br />%';

-- LF variant
UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT(
        '<strong><a href="{{ confirmLink }}" target="_blank">Termin aktivieren</a></strong>  <br /><br />',
        CHAR(10), CHAR(10),
        '<br>', CHAR(10),
        '——', CHAR(10),
        '<br><br>'
    ),
    CONCAT(
        '<strong><a href="{{ confirmLink }}" target="_blank">Termin aktivieren</a></strong>',
        CHAR(10),
        '<br>', CHAR(10),
        '—', CHAR(10),
        '<br>'
    )
)
WHERE `name` = 'mail_preconfirmed.twig'
  AND `value` LIKE '%Termin aktivieren%</strong>  <br /><br />%'
  AND `value` LIKE '%——%';

-- 3) Bestätigung/Erinnerung: tighten space below Ort (hint endif) before separator (CRLF)
UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT(
        '{% endif %}<br />', CHAR(13), CHAR(10),
        '<br><br>', CHAR(13), CHAR(10),
        '——', CHAR(13), CHAR(10),
        '<br><br>'
    ),
    CONCAT(
        '{% endif %}<br />', CHAR(13), CHAR(10),
        '<br>', CHAR(13), CHAR(10),
        '—', CHAR(13), CHAR(10),
        '<br>'
    )
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%{% endif %}<br />%——%';

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT(
        '{% endif %}<br />', CHAR(10),
        '<br><br>', CHAR(10),
        '——', CHAR(10),
        '<br><br>'
    ),
    CONCAT(
        '{% endif %}<br />', CHAR(10),
        '<br>', CHAR(10),
        '—', CHAR(10),
        '<br>'
    )
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%{% endif %}<br />%——%';

-- 4) Bestätigung/Erinnerung: tighten space below bullet list before separator (CRLF)
UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT(
        '</ul>', CHAR(13), CHAR(10),
        '<br>', CHAR(13), CHAR(10),
        '——', CHAR(13), CHAR(10),
        '<br><br>'
    ),
    CONCAT(
        '</ul>', CHAR(13), CHAR(10),
        '<br>', CHAR(13), CHAR(10),
        '—', CHAR(13), CHAR(10),
        '<br>'
    )
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%</ul>%——%';

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT(
        '</ul>', CHAR(10),
        '<br>', CHAR(10),
        '——', CHAR(10),
        '<br><br>'
    ),
    CONCAT(
        '</ul>', CHAR(10),
        '<br>', CHAR(10),
        '—', CHAR(10),
        '<br>'
    )
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%</ul>%——%';

-- 5) Remaining double-br + double-em-dash blocks → single br + single em-dash (CRLF / LF)
UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT('<br><br>', CHAR(13), CHAR(10), '——', CHAR(13), CHAR(10), '<br><br>'),
    CONCAT('<br>', CHAR(13), CHAR(10), '—', CHAR(13), CHAR(10), '<br>')
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%<br><br>%——%<br><br>%';

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT('<br><br>', CHAR(10), '——', CHAR(10), '<br><br>'),
    CONCAT('<br>', CHAR(10), '—', CHAR(10), '<br>')
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%<br><br>%——%<br><br>%';

-- Indented intro separators (confirmation/reminder opening block)
UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT('<br><br>', CHAR(13), CHAR(10), '        ——', CHAR(13), CHAR(10), '        <br><br>'),
    CONCAT('<br>', CHAR(13), CHAR(10), '        —', CHAR(13), CHAR(10), '        <br>')
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%        ——%';

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT('<br><br>', CHAR(10), '        ——', CHAR(10), '        <br><br>'),
    CONCAT('<br>', CHAR(10), '        —', CHAR(10), '        <br>')
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%        ——%';

-- 6) Catch-all: any remaining double Geviertstrich → single
UPDATE `mailtemplate`
SET `value` = REPLACE(`value`, '——', '—')
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%——%';

COMMIT;
