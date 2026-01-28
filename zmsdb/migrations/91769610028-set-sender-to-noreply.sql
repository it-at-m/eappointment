UPDATE `email`
SET `absenderadresse` = 'noreply-terminvereinbarung@muenchen.de';

UPDATE `preferences`
SET `value` = 'noreply-terminvereinbarung@muenchen.de';
WHERE `name` = 'emailFrom';