-- Remove unused column from behoerde (never read or written by application)
ALTER TABLE `behoerde`
  DROP COLUMN `IPProtectZeit`;
