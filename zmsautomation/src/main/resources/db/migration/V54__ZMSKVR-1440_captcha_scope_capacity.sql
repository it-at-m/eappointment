-- ZMSKVR-1440: keep captcha scope 74 (office 10427) bookable under full-shard load.
--
-- V42 planted one rolling 6-hour window with only 5 internet seats. Under parallel
-- Chrome/Firefox/Edge citizenview shards those seats are drained before the
-- reservation/captcha-session scenarios reach Weiter, so highlight fails even though
-- the Altcha inject already succeeded.
--
-- Replace that row with a full-day, high-capacity window for the next two weeks.
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin.

DELETE FROM `oeffnungszeit`
WHERE `OeffnungszeitID` = 136216
   OR (`StandortID` = 74 AND `kommentar` LIKE 'ZMSKVR-1440%');

SET @range_start := CURDATE();
SET @range_end := DATE_ADD(CURDATE(), INTERVAL 14 DAY);

INSERT INTO `oeffnungszeit`
(
  `OeffnungszeitID`,
  `StandortID`,
  `Startdatum`,
  `Endedatum`,
  `allexWochen`,
  `jedexteWoche`,
  `Wochentag`,
  `Anfangszeit`,
  `Terminanfangszeit`,
  `Endzeit`,
  `Terminendzeit`,
  `Timeslot`,
  `Anzahlarbeitsplaetze`,
  `Anzahlterminarbeitsplaetze`,
  `kommentar`,
  `reduktionTermineImInternet`,
  `erlaubemehrfachslots`,
  `Offen_ab`,
  `Offen_bis`,
  `updateTimestamp`
)
VALUES
(
  136216,
  74,
  @range_start,
  @range_end,
  1,
  0,
  127,
  '00:00:00',
  '08:00:00',
  '00:00:00',
  '20:00:00',
  '00:05:00',
  0,
  30,
  'ZMSKVR-1440 Captcha Öffnungszeit (shard capacity)',
  0,
  5,
  0,
  30,
  NOW()
);
