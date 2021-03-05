ALTER TABLE `nutzerzuordnung` DROP PRIMARY KEY, ADD PRIMARY KEY(`nutzerid`, `behoerdenid`);
ALTER TABLE `source` DROP PRIMARY KEY, ADD PRIMARY KEY(`source`);