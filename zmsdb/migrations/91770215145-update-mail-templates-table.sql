START TRANSACTION;

-- 1) In iCalendar-Templates METHOD:REQUEST -> METHOD:PUBLISH
UPDATE `mailtemplate`
SET `value` = REPLACE(`value`, 'METHOD:REQUEST', 'METHOD:PUBLISH')
WHERE `name` IN ('icsappointment.twig', 'icsappointment_delete.twig')
  AND `value` LIKE '%METHOD:REQUEST%';

-- 2) remove JSON-LD <script>-block
UPDATE `mailtemplate`
SET `value` = CONCAT(
    SUBSTRING(`value`, 1, LOCATE('<script type="application/ld+json">', `value`) - 1),
    SUBSTRING(
        `value`,
        LOCATE(
            '</script>',
            `value`,
            LOCATE('<script type="application/ld+json">', `value`)
        ) + LENGTH('</script>')
    )
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_delete.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND LOCATE('<script type="application/ld+json">', `value`) > 0;

-- 3) remove blank space between </div> and {% endblock %}
UPDATE `mailtemplate`
SET `value` = REPLACE(
    REPLACE(`value`,
        '</div>\r\n\r\n{% endblock %}',
        '</div>\r\n{% endblock %}'
    ),
    '</div>\n\n{% endblock %}',
    '</div>\n{% endblock %}'
)
WHERE `name` IN (
    'mail_confirmation.twig',
    'mail_delete.twig',
    'mail_preconfirmed.twig',
    'mail_reminder.twig'
)
  AND `value` LIKE '%</div>%{% endblock %}%';

COMMIT;
