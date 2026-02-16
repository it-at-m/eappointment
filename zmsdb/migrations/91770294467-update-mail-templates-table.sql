START TRANSACTION;

-- 1) remove icsappointment.twig: METHOD:REQUEST
UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT('METHOD:REQUEST', CHAR(13), CHAR(10)),
    ''
)
WHERE `name` = 'icsappointment.twig'
  AND `value` LIKE '%METHOD:REQUEST%';

-- 2) remove icsappointment.twig: ORGANIZER + SEQUENCE
UPDATE `mailtemplate`
SET `value` = REPLACE(
    `value`,
    CONCAT(
        'ORGANIZER;CN="{{ process.scope.provider.displayName }}":MAILTO:{{ process.scope.preferences.client.emailFrom }}',
        CHAR(13), CHAR(10),
        'SEQUENCE:0',
        CHAR(13), CHAR(10)
    ),
    ''
)
WHERE `name` = 'icsappointment.twig'
  AND `value` LIKE '%ORGANIZER;CN="{{ process.scope.provider.displayName }}":MAILTO:{{ process.scope.preferences.client.emailFrom }}%';

-- 3) icsappointment_delete.twig: METHOD:REQUEST -> METHOD:CANCEL
UPDATE `mailtemplate`
SET `value` = REPLACE(`value`, 'METHOD:REQUEST', 'METHOD:CANCEL')
WHERE `name` = 'icsappointment_delete.twig'
  AND `value` LIKE '%METHOD:REQUEST%';

-- 4) remove JSON-LD <script>-block
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

-- 5) remove blank space between </div> and {% endblock %}
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
