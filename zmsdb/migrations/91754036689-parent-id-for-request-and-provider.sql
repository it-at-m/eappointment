ALTER TABLE `request`
    ADD COLUMN `parent_id` varchar(20) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,

ALTER TABLE `provider`
    ADD COLUMN `parent_id` varchar(20) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
