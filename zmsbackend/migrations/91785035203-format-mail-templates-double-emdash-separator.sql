START TRANSACTION;

-- ZMSKVR-352 follow-up: use a double Geviertstrich with two <br> on each side:
-- a double Geviertstrich with two <br> on each side:
--
--   <br><br>
--   ——
--   <br><br>
--
-- 91785035201/35202 already ran on some envs and left a single — with one
-- <br>. A lone —— can also wrap onto two lines in some clients, so wrap it
-- in nowrap. Idempotent: the canonical block matches itself.
--
-- Does not touch long ASCII rules (----------------------).

-- Standalone separator line (—, ——, ---, optional nowrap span) plus any
-- surrounding <br> tags → canonical two-break nowrap double Geviertstrich.
UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    CONCAT(
        '(<br[[:space:]]*/?>[[:space:]]*)*',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')+',
        '[[:blank:]]*(<span style="white-space:nowrap">)?(——|—|---)(</span>)?[[:blank:]]*',
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
    'mail_reminder.twig'
)
  AND `value` REGEXP CONCAT(
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')',
        '[[:blank:]]*(<span style="white-space:nowrap">)?(——|—|---)(</span>)?[[:blank:]]*',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')'
    );

COMMIT;
