ALTER TABLE `request`
    ADD COLUMN `variant_id` INT UNSIGNED DEFAULT NULL,
ADD CONSTRAINT `fk_request_variant` FOREIGN KEY (`variant_id`) REFERENCES `request_variant`(`id`);
