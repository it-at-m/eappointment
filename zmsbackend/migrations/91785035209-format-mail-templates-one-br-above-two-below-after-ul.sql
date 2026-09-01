START TRANSACTION;

-- ZMSKVR-352: after the Hinweise list the separator must be:
--
--   </ul>
--   <br>        ← one above the line
--   <span>——</span>
--   <br><br>    ← two below the line
--
-- 91785035208 left 1+1 there. Every other separator stays two+two.

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
        '</ul>',
        '[[:space:]]*',
        '(<br[[:space:]]*/?>[[:space:]]*)*',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')*',
        '[[:blank:]]*<span style="white-space:nowrap">——</span>[[:blank:]]*',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')+',
        '([[:blank:]]*<br[[:space:]]*/?>[[:space:]]*)*'
    ),
    CONCAT(
        '</ul>',
        CHAR(13), CHAR(10),
        '<br>',
        CHAR(13), CHAR(10),
        '<span style="white-space:nowrap">——</span>',
        CHAR(13), CHAR(10),
        '<br><br>'
    )
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` REGEXP CONCAT(
        '</ul>[[:space:]]*(<br[[:space:]]*/?>[[:space:]]*)*',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')*',
        '[[:blank:]]*<span style="white-space:nowrap">——</span>'
    );

COMMIT;
