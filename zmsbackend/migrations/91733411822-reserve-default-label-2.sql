BEGIN;

UPDATE `standort`
SET `custom_text_field_label` = 'Zusätzliche Bemerkungen'
WHERE `custom_text_field_label` = '' OR `custom_text_field_label` IS NULL OR `custom_text_field_label` = 'Zusätzliche Bemerkungen (für Bürger*innen sichtbar)';

COMMIT;