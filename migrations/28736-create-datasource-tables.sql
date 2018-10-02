DROP TABLE IF EXISTS `provider`;
CREATE TABLE `provider` (
	`source` VARCHAR(10) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
	`id` VARCHAR(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
	`name` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`contact__city` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`contact__country` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`contact__lat` FLOAT NOT NULL ,
	`contact__lon` FLOAT NOT NULL ,
	`contact__postalCode` INT(5) NOT NULL ,
	`contact__region` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`contact__street` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`contact__streetNumber` VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`link` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`data` TEXT NOT NULL,
PRIMARY KEY (`source`, `id`))
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `request`;
CREATE TABLE `request` (
	`source` VARCHAR(10) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
	`id` VARCHAR(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
	`name` VARCHAR(1000) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`link` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`group` VARCHAR(200) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,
	`data` TEXT NOT NULL,
PRIMARY KEY (`source`, `id`))
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci;

DROP TABLE IF EXISTS `request_provider`;
CREATE TABLE `request_provider` ( 
	`source` VARCHAR(10) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
	`request__id` VARCHAR(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
	`provider__id` VARCHAR(20) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
	`slots` FLOAT NOT NULL DEFAULT 0,
PRIMARY KEY (`source`, `request__id`, `provider__id`))
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci;
