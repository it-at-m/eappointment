-- Add zms source if it doesn't exist
INSERT INTO `source` (`source`, `label`, `editable`, `contact__name`, `contact__email`)
SELECT "zms", "Varianten", 1, "ZMS", "noreply@muenchen.de"
WHERE NOT EXISTS (
    SELECT 1 FROM `source` WHERE `source` = "zms"
);

