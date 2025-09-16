ALTER TABLE `request`
DROP FOREIGN KEY `fk_request_variant`;

ALTER TABLE `request`
DROP FOREIGN KEY `fk_request_parent`;

ALTER TABLE `provider`
DROP FOREIGN KEY `fk_provider_parent`;
