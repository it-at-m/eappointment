ALTER TABLE buerger 
ADD COLUMN `custom_text_field2` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL;

ALTER TABLE standort 
ADD COLUMN `custom_text_field2_label` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
ADD COLUMN `custom_text_field2_active` INT(5) NOT NULL DEFAULT 0,
ADD COLUMN `custom_text_field2_required` INT(5) NOT NULL DEFAULT 0;
