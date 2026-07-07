ALTER TABLE `request_variant`
ADD COLUMN language VARCHAR(10) NOT NULL DEFAULT 'de' AFTER name;
