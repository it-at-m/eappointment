START TRANSACTION;

-- ZMSKVR-352: 91785035207 also rewrote the Ort → Hinweise separator to
-- one <br> on each side. Only the separator AFTER the Hinweise </ul>
-- should be 1+1. Restore two <br> on every other separator, then put
-- 1+1 back only after </ul>.

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
        '<br>'
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
