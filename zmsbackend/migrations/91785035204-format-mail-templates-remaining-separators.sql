START TRANSACTION;

-- ZMSKVR-352 follow-up: remaining mail templates still use mixed
-- standalone separator lines that 91785035203 did not cover:
--
--   <br><br>                 (mail_delete.twig)
--   —
--   <br><br>
--
--   <br><br>                 (mail_delete.twig)
--   ---
--   <br><br>
--
--   <br /><br />             (mail_queued.twig and customized
--   ----------------------    mail_confirmation.twig; indented)
--   <br /><br />
--
-- 35203 only matched (——|—|---) on confirmation/preconfirmed/reminder,
-- so long ASCII rules and delete/queued copies were left behind.
-- mail_admin_delete.twig is included so customized copies with dashes
-- are converted too (current dump rows have none).
--
-- Same canonical block as 91785035203. Idempotent: that block matches
-- itself. Standalone line only (newline-bounded), so hyphens inside
-- URLs are not touched.
--
-- Dash forms (longer first; no —+ because MariaDB REGEXP is byte-based):
--   ——  double Geviertstrich
--   —   single Geviertstrich
--   -{3,}  ---, ----------------------, and any longer ASCII rule
--   ––  double Halbgeviertstrich
--   –   single Halbgeviertstrich

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

COMMIT;
