ALTER TABLE buerger 
ADD COLUMN `custom_text_field` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;

ALTER TABLE standort 
ADD COLUMN `custom_text_field_label` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
ADD COLUMN `custom_text_field_active` INT(5) NOT NULL DEFAULT 0,
ADD COLUMN `custom_text_field_required` INT(5) NOT NULL DEFAULT 0;
