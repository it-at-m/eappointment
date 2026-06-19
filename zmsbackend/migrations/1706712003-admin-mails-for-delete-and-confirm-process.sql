ALTER TABLE standort 
ADD COLUMN `admin_mail_on_appointment` INT(5) NOT NULL DEFAULT 0,
ADD COLUMN `admin_mail_on_deleted` INT(5) NOT NULL DEFAULT 0;