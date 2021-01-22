ALTER TABLE `clusterzuordnung` ADD PRIMARY KEY (`standortID`);
ALTER TABLE `nutzerzuordnung` ADD PRIMARY KEY (`nutzerid`);
ALTER TABLE `nutzerzuordnung` ADD INDEX `departmentuser` (`behoerdenid`, `nutzerid`);
ALTER TABLE `source` ADD PRIMARY KEY (`source`);