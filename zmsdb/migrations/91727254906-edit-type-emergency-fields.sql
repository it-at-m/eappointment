ALTER TABLE `standort` CHANGE `notrufinitiierung` `notrufinitiierung` VARCHAR(8) NULL DEFAULT NULL;
ALTER TABLE `standort` CHANGE `notrufantwort` `notrufantwort` VARCHAR(8) NULL DEFAULT NULL;
ALTER TABLE `nutzer` CHANGE `notrufinitiierung` `notrufinitiierung` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
ALTER TABLE `nutzer` CHANGE `notrufantwort` `notrufantwort` VARCHAR(8) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;
