START TRANSACTION;

-- ZMSKVR-352: Hinweise <ul> has Outlook default bottom margin, which
-- stacks on the two <br> after </ul> and leaves a large gap above the
-- following separator.
--
-- 1) margin:0; padding-bottom:0px on the Hinweise list.
-- 2) One <br> above and one <br> below the separator that sits next to
--    that list (after </ul> and before the Hinweise heading).

UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    '(Hinweise zur Vorbereitung:</strong>|Hinweise zu Ihrem Besuch:</strong>|Hinweise zu unserem Telefonat:</strong>)[[:space:]]*<ul>',
    CONCAT('\\1', CHAR(13), CHAR(10), '<ul style="margin:0;padding-bottom:0px">')
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` REGEXP 'Hinweise[^<]*</strong>[[:space:]]*<ul>';

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

UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    CONCAT(
        '(<br[[:space:]]*/?>[[:space:]]*)*',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')+',
        '[[:blank:]]*<span style="white-space:nowrap">——</span>[[:blank:]]*',
        '(', CHAR(13), CHAR(10), '|', CHAR(10), ')+',
        '([[:blank:]]*<br[[:space:]]*/?>[[:space:]]*)*',
        '(<strong>[[:space:]]*Hinweise)'
    ),
    CONCAT(
        '<br>',
        CHAR(13), CHAR(10),
        '<span style="white-space:nowrap">——</span>',
        CHAR(13), CHAR(10),
        '<br>',
        CHAR(13), CHAR(10),
        '\\5'
    )
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` REGEXP CONCAT(
        '<span style="white-space:nowrap">——</span>[[:space:]]*',
        '(<br[[:space:]]*/?>[[:space:]]*)*',
        '<strong>[[:space:]]*Hinweise'
    );

COMMIT;
