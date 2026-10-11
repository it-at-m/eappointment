ALTER TABLE standort
    ADD COLUMN IF NOT EXISTS `rebooking_disabled` int(5) NOT NULL DEFAULT 0;
