-- Widen availability_history.summary for full form snapshots (ZMSKVR-1249).
ALTER TABLE `availability_history`
    MODIFY COLUMN `summary` TEXT NOT NULL;
