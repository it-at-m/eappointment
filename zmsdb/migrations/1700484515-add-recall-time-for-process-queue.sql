ALTER TABLE buerger 
ADD COLUMN `recall_time` time DEFAULT NULL;

ALTER TABLE standort 
ADD COLUMN `recall_time_limit` INT(5) NOT NULL DEFAULT 5;