START TRANSACTION;

-- ZMSKVR-1620: Confirmation and reminder mails link to "Termin absagen oder verschieben".
-- When a scope disables rescheduling, that link must say "Termin absagen".
-- Affects the global row and every customized copy of these two templates.
-- Idempotent: rows that already contain the condition are left unchanged.

UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    '<br><a href="{{ config.appointments.urlAppointments }}#/appointment/{{ appointmentLink }}"><strong>Termin absagen oder verschieben</strong></a><br />',
    '{% if process.scope.preferences.appointment.rebookingDisabled|default(false) %}<br><a href="{{ config.appointments.urlAppointments }}#/appointment/{{ appointmentLink }}"><strong>Termin absagen</strong></a><br />{% else %}<br><a href="{{ config.appointments.urlAppointments }}#/appointment/{{ appointmentLink }}"><strong>Termin absagen oder verschieben</strong></a><br />{% endif %}'
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%<br><a href="{{ config.appointments.urlAppointments }}#/appointment/{{ appointmentLink }}"><strong>Termin absagen oder verschieben</strong></a><br />%'
  AND `value` NOT LIKE '%rebookingDisabled%';

COMMIT;
