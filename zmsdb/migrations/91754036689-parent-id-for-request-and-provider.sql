ALTER TABLE `request`
    ADD COLUMN `parent_id` varchar(20) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
ADD CONSTRAINT `fk_request_parent` FOREIGN KEY (`parent_id`) REFERENCES `request`(`id`);

ALTER TABLE `provider`
    ADD COLUMN `parent_id` varchar(20) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
ADD CONSTRAINT `fk_provider_parent` FOREIGN KEY (`parent_id`) REFERENCES `provider`(`id`);
