ALTER TABLE `clusterzuordnung` ADD PRIMARY KEY (`clusterID`, `standortID`);
ALTER TABLE `nutzerzuordnung` ADD PRIMARY KEY (`nutzerid`, `behoerdenid`);
ALTER TABLE `source` ADD PRIMARY KEY (`source`);