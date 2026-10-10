-- Yesterday's Dienstleistungsstatistik for two quiet Feuerwachen.
-- The nightly archive job is not part of this run, so the rows are inserted directly.
--
-- StandortID 274 is Feuerwache 8 - Föhring, 271 is Feuerwache 7 - Milbertshofen.
-- Service 10389330 is "Führungen auf den Feuerwachen".
-- anliegenid -1 is "Dienstleistung wurde nicht erfasst".
-- anliegenid 0 is "Dienstleistung konnte nicht erbracht werden".
--
-- Feuerwache 8: Führungen 2 x 10 min, nicht erfasst 1 x 20 min, nicht erbracht 1 x 16 min.
-- Both stations: Führungen 3 x 10 min, nicht erfasst 2 x 20 min, nicht erbracht 2 x 16 min.
-- The Summe counts every row. The footer Ø weights every row that has a duration.
-- Feuerwache 8: (10*2 + 20 + 16) / 4 = 14:00, Summe 4.
-- Both stations: (10*3 + 20*2 + 16*2) / 7 = 14:34, Summe 7.

SET @stats_date := DATE_SUB(CURDATE(), INTERVAL 1 DAY);

DELETE FROM `statistik`
WHERE `standortid` IN (274, 271)
  AND `datum` = @stats_date;

INSERT INTO `statistik`
(
  `kundenid`,
  `organisationsid`,
  `behoerdenid`,
  `clusterid`,
  `standortid`,
  `anliegenid`,
  `datum`,
  `lastbuergerarchivid`,
  `termin`,
  `info_dl_id`,
  `bearbeitungszeit`
)
VALUES
  (1, 1, 29, 1, 274, 10389330, @stats_date, 0, 0, 10389330, 10),
  (1, 1, 29, 1, 274, 10389330, @stats_date, 0, 0, 10389330, 10),
  (1, 1, 29, 1, 274, -1,       @stats_date, 0, 0, 0,        20),
  (1, 1, 29, 1, 274, 0,        @stats_date, 0, 0, 0,        16),
  (1, 1, 29, 1, 271, 10389330, @stats_date, 0, 0, 10389330, 10),
  (1, 1, 29, 1, 271, -1,       @stats_date, 0, 0, 0,        20),
  (1, 1, 29, 1, 271, 0,        @stats_date, 0, 0, 0,        16);
