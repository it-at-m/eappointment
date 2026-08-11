START TRANSACTION;

-- ZMSKVR-1557: Follow-up for templates that use a different "Zeit" block layout
-- than 91785015571 (no spaces inside <strong>, date on its own line after <br>).
--
-- Test/customized example:
--   <strong>Zeit:</strong>
--   <br>
--   {{ (date|default(getNow))|format_date(locale="de", pattern="EEEE, dd.MM.yyyy, HH:mm 'Uhr'") }}
--
-- Uses REGEXP_REPLACE so indentation / CRLF vs LF between the three lines does not matter.
-- Idempotent with the first migration via NOT LIKE '%Voraussichtliche Termindauer%'.

UPDATE `mailtemplate`
SET `value` = REGEXP_REPLACE(
    `value`,
    '<strong>Zeit:</strong>([[:space:]]*<br>[[:space:]]*)(\\{\\{ \\(date\\|default\\(getNow\\)\\)\\|format_date\\(locale="de", pattern="EEEE, dd\\.MM\\.yyyy, HH:mm ''Uhr''"\\) \\}\\})',
    CONCAT(
        '{% set appointment = process.appointments|first %}', CHAR(10),
        '{% set slotTimeInMinutes = appointment.availability.slotTimeInMinutes|default(process.scope.provider.data.slotTimeInMinutes) %}', CHAR(10),
        '{% set estimatedDuration = slotTimeInMinutes * appointment.slotCount %}', CHAR(10),
        '<strong>Zeit:</strong>\\1\\2', CHAR(10),
        '{% if estimatedDuration %}<br>Voraussichtliche Termindauer: {{ estimatedDuration }} Minuten{% endif %}'
    )
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%<strong>Zeit:</strong>%'
  AND `value` LIKE '%format_date(locale="de", pattern="EEEE, dd.MM.yyyy, HH:mm ''Uhr''")%'
  AND `value` NOT LIKE '%Voraussichtliche Termindauer%';

COMMIT;
