START TRANSACTION;

-- ZMSKVR-352 follow-up: 91785035203/35204 used two <br> on each side of
-- the nowrap double Geviertstrich. Combined with <br> already in the
-- surrounding markup (after Ort/hint, after </ul>, before Hinweise <ul>)
-- Outlook shows a large empty band above and below the separator.
--
-- Test confirmation (pretty-printed, matches the Bürgerbüro screenshot):
--
--   {% endif %}
--   <br><br>
--   <span style="white-space:nowrap">——</span>
--   <br><br><strong>Hinweise zur Vorbereitung:</strong>
--   <br>
--   <ul>…</ul>
--   <br><br>
--   <span style="white-space:nowrap">——</span>
--   <br><br>Ihnen ist etwas dazwischengekommen?
--
-- Dev confirmation is the same 2+2 block, plus an extra <br /> on the
-- hint line before {% endif %}<br><br>.
--
-- Collapse surrounding <br> to a single break on each side. Same dash
-- matcher as 35204 so leftover --- / ---------------------- still
-- convert. Idempotent: the 1+1 canonical block matches itself.

UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    CONCAT(
        '(<br[[:space:]]*/?>[[:space:]]*)*',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')+',
        '[[:blank:]]*(<span style="white-space:nowrap">)?(——|—|-{3,}|––|–)(</span>)?[[:blank:]]*',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')+',
        '([[:blank:]]*<br[[:space:]]*/?>[[:space:]]*)*'
    ),
    CONCAT(
        '<br>',
        CHAR(13), CHAR(10),
        '<span style="white-space:nowrap">——</span>',
        CHAR(13), CHAR(10),
        '<br>'
    )
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig',
    'mail_delete.twig',
    'mail_admin_delete.twig',
    'mail_queued.twig'
)
  AND `value` REGEXP CONCAT(
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')',
        '[[:blank:]]*(<span style="white-space:nowrap">)?(——|—|-{3,}|––|–)(</span>)?[[:blank:]]*',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')'
    );

-- Extra blank line between the Hinweise heading and the list, left
-- after the separator's trailing <br><br> was reduced.
UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    CONCAT(
        '(Hinweise zur Vorbereitung:</strong>|Hinweise zu Ihrem Besuch:</strong>|Hinweise zu unserem Telefonat:</strong>)',
        '[[:space:]]*<br[[:space:]]*/?>[[:space:]]*',
        '<ul>'
    ),
    CONCAT('\\1', CHAR(13), CHAR(10), '<ul>')
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` REGEXP 'Hinweise[^<]*</strong>[[:space:]]*<br[[:space:]]*/?>[[:space:]]*<ul>';

COMMIT;
