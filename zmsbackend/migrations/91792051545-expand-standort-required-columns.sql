-- Expand phase

ALTER TABLE `standort`
    ADD COLUMN IF NOT EXISTS `email_required`
        INT(5) UNSIGNED NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS `telephone_required`
        INT(5) UNSIGNED NOT NULL DEFAULT 0;

DELIMITER $$

DROP TRIGGER IF EXISTS `standort_required_fields_before_insert`$$

CREATE TRIGGER `standort_required_fields_before_insert`
BEFORE INSERT ON `standort`
FOR EACH ROW
BEGIN
    -- On INSERT an omitted column has its default value 0.
    -- Therefore, the non-default value identifies the writing code version.
    IF NEW.`emailPflichtfeld` <> NEW.`email_required` THEN
        IF NEW.`emailPflichtfeld` = 0 THEN
            SET NEW.`emailPflichtfeld` = NEW.`email_required`;
        ELSEIF NEW.`email_required` = 0 THEN
            SET NEW.`email_required` = NEW.`emailPflichtfeld`;
        ELSE
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Conflicting email-required values';
        END IF;
    END IF;

    IF NEW.`telefonPflichtfeld` <> NEW.`telephone_required` THEN
        IF NEW.`telefonPflichtfeld` = 0 THEN
            SET NEW.`telefonPflichtfeld` = NEW.`telephone_required`;
        ELSEIF NEW.`telephone_required` = 0 THEN
            SET NEW.`telephone_required` = NEW.`telefonPflichtfeld`;
        ELSE
            SIGNAL SQLSTATE '45000'
                SET MESSAGE_TEXT = 'Conflicting telephone-required values';
        END IF;
    END IF;
END$$

DROP TRIGGER IF EXISTS `standort_required_fields_before_update`$$

CREATE TRIGGER `standort_required_fields_before_update`
BEFORE UPDATE ON `standort`
FOR EACH ROW
BEGIN
    IF NEW.`emailPflichtfeld` <> OLD.`emailPflichtfeld`
       AND NEW.`email_required` = OLD.`email_required` THEN
        -- Legacy code changed the old column.
        SET NEW.`email_required` = NEW.`emailPflichtfeld`;
    ELSEIF NEW.`email_required` <> OLD.`email_required`
       AND NEW.`emailPflichtfeld` = OLD.`emailPflichtfeld` THEN
        -- New code changed the new column.
        SET NEW.`emailPflichtfeld` = NEW.`email_required`;
    ELSEIF NEW.`emailPflichtfeld` <> OLD.`emailPflichtfeld`
       AND NEW.`email_required` <> OLD.`email_required`
       AND NEW.`emailPflichtfeld` <> NEW.`email_required` THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Conflicting email-required update';
    ELSEIF NEW.`emailPflichtfeld` <> NEW.`email_required` THEN
        -- Heals a row changed between ADD COLUMN and trigger creation.
        SET NEW.`email_required` = NEW.`emailPflichtfeld`;
    END IF;

    IF NEW.`telefonPflichtfeld` <> OLD.`telefonPflichtfeld`
       AND NEW.`telephone_required` = OLD.`telephone_required` THEN
        SET NEW.`telephone_required` = NEW.`telefonPflichtfeld`;
    ELSEIF NEW.`telephone_required` <> OLD.`telephone_required`
       AND NEW.`telefonPflichtfeld` = OLD.`telefonPflichtfeld` THEN
        SET NEW.`telefonPflichtfeld` = NEW.`telephone_required`;
    ELSEIF NEW.`telefonPflichtfeld` <> OLD.`telefonPflichtfeld`
       AND NEW.`telephone_required` <> OLD.`telephone_required`
       AND NEW.`telefonPflichtfeld` <> NEW.`telephone_required` THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Conflicting telephone-required update';
    ELSEIF NEW.`telefonPflichtfeld` <> NEW.`telephone_required` THEN
        SET NEW.`telephone_required` = NEW.`telefonPflichtfeld`;
    END IF;
END$$

DELIMITER ;

-- The triggers are already active while the backfill runs.
UPDATE `standort`
SET
    `email_required` = `emailPflichtfeld`,
    `telephone_required` = `telefonPflichtfeld`,
    `updateTimestamp` = `updateTimestamp`;
