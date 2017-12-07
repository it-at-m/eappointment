ALTER TABLE `statistik` ADD INDEX `scopedate` (`standortid`,`datum`);
ALTER TABLE `statistik` ADD INDEX `departmentdate` (`behoerdenid`,`datum`);
ALTER TABLE `statistik` ADD INDEX `organisationdate` (`organisationsid`,`datum`);
ALTER TABLE `buergerarchiv` ADD INDEX `scopedate` (`StandortID`,`datum`);
ALTER TABLE `buergerarchiv` ADD INDEX `scopemissed` (`nicht_erschienen`,`StandortID`);
ALTER TABLE `buergerarchiv` ADD INDEX `scopeappointment` (`mitTermin`,`StandortID`);
