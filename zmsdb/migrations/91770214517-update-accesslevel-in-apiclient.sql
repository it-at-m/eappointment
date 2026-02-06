UPDATE `apiclient`
SET `accesslevel` = 'intern'
WHERE `accesslevel` = 'callcenter';

ALTER TABLE `apiclient`
  MODIFY COLUMN `accesslevel`
    enum('public','intern','blocked') DEFAULT 'public';
