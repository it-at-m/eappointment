START TRANSACTION;

-- ZMSKVR-352: exactly two <br> above and two <br> below the separator
-- in every mail template. 91785035205 collapsed that to one on each
-- side, so live HTML now has a single <br> under the line (and still
-- two above when an Ort/hint <br> sits in front of the 1-break block).
--
-- 1) Consume 1/2/3+ adjacent <br> and rewrite to:
--
--      <br><br>
--      <span style="white-space:nowrap">——</span>
--      <br><br>
--
-- 2) Drop extra <br> immediately before {% if process.scope.hint %}
--    when that block sits directly above the separator. Those breaks
--    are still rendered when hint is empty and would make three above.
-- 3) Drop <br> after the hint <strong> before {% endif %} (same stack).

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
        '<br><br>',
        CHAR(13), CHAR(10),
        '<span style="white-space:nowrap">——</span>',
        CHAR(13), CHAR(10),
        '<br><br>'
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

UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    CONCAT(
        '(<br[[:space:]]*/?>[[:space:]]*)+',
        '([{]%[[:space:]]*if process[.]scope[.]hint[[:space:]]*%[}])'
    ),
    '\\2'
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` REGEXP CONCAT(
        '<br[[:space:]]*/?>[[:space:]]*',
        '[{]%[[:space:]]*if process[.]scope[.]hint[[:space:]]*%[}]'
    );

UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    CONCAT(
        '(</strong>)',
        '[[:space:]]*<br[[:space:]]*/?>[[:space:]]*',
        '([{]%[[:space:]]*endif[[:space:]]*%[}])'
    ),
    CONCAT('\\1', CHAR(13), CHAR(10), '\\2')
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` REGEXP CONCAT(
        '</strong>[[:space:]]*<br[[:space:]]*/?>[[:space:]]*',
        '[{]%[[:space:]]*endif[[:space:]]*%[}]'
    );

COMMIT;
