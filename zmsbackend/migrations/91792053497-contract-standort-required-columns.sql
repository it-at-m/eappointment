-- Contract phase

DROP TRIGGER IF EXISTS `standort_required_fields_before_insert`;
DROP TRIGGER IF EXISTS `standort_required_fields_before_update`;

ALTER TABLE `standort`
    DROP COLUMN IF EXISTS `emailPflichtfeld`,
    DROP COLUMN IF EXISTS `telefonPflichtfeld`;
