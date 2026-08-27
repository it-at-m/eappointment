START TRANSACTION;

-- ZMSKVR-1557: Add estimated appointment duration to confirmation and reminder mails.
--
-- Matches mock templates: after the datetime under "Zeit", show
--   Voraussichtliche Termindauer: <n> Minuten
--
-- Duration = slotTimeInMinutes * slotCount (availability, with provider.data fallback).
-- Affects global and customized templates that use the Munich "Zeit" block.
-- Legacy templates without that block are left unchanged.

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT(
        '<strong> Zeit: </strong>', CHAR(13), CHAR(10),
        '<br>{{ (date|default(getNow))|format_date(locale="de", pattern="EEEE, dd.MM.yyyy, HH:mm ''Uhr''") }}'
    ),
    CONCAT(
        '{% set appointment = process.appointments|first %}', CHAR(13), CHAR(10),
        '{% set slotTimeInMinutes = appointment.availability.slotTimeInMinutes|default(process.scope.provider.data.slotTimeInMinutes) %}', CHAR(13), CHAR(10),
        '{% set estimatedDuration = slotTimeInMinutes * appointment.slotCount %}', CHAR(13), CHAR(10),
        '<strong> Zeit: </strong>', CHAR(13), CHAR(10),
        '<br>{{ (date|default(getNow))|format_date(locale="de", pattern="EEEE, dd.MM.yyyy, HH:mm ''Uhr''") }}', CHAR(13), CHAR(10),
        '{% if estimatedDuration %}<br>Voraussichtliche Termindauer: {{ estimatedDuration }} Minuten{% endif %}'
    )
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%<strong> Zeit: </strong>%'
  AND `value` LIKE '%format_date(locale="de", pattern="EEEE, dd.MM.yyyy, HH:mm ''Uhr''")%'
  AND `value` NOT LIKE '%Voraussichtliche Termindauer%';

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT(
        '<strong> Zeit: </strong>', CHAR(10),
        '<br>{{ (date|default(getNow))|format_date(locale="de", pattern="EEEE, dd.MM.yyyy, HH:mm ''Uhr''") }}'
    ),
    CONCAT(
        '{% set appointment = process.appointments|first %}', CHAR(10),
        '{% set slotTimeInMinutes = appointment.availability.slotTimeInMinutes|default(process.scope.provider.data.slotTimeInMinutes) %}', CHAR(10),
        '{% set estimatedDuration = slotTimeInMinutes * appointment.slotCount %}', CHAR(10),
        '<strong> Zeit: </strong>', CHAR(10),
        '<br>{{ (date|default(getNow))|format_date(locale="de", pattern="EEEE, dd.MM.yyyy, HH:mm ''Uhr''") }}', CHAR(10),
        '{% if estimatedDuration %}<br>Voraussichtliche Termindauer: {{ estimatedDuration }} Minuten{% endif %}'
    )
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%<strong> Zeit: </strong>%'
  AND `value` LIKE '%format_date(locale="de", pattern="EEEE, dd.MM.yyyy, HH:mm ''Uhr''")%'
  AND `value` NOT LIKE '%Voraussichtliche Termindauer%';

COMMIT;
