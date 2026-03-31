ALTER TABLE `buerger`
    ADD COLUMN `is_ticketprinter` tinyint(1) NOT NULL DEFAULT 0;

ALTER TABLE `buergerarchiv`
    ADD COLUMN `is_ticketprinter` tinyint(1) NOT NULL DEFAULT 0;

ALTER TABLE `buergerarchivtoday`
    ADD COLUMN `is_ticketprinter` tinyint(1) NOT NULL DEFAULT 0;

