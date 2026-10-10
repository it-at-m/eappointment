-- ZMSKVR-1440: keep captcha scope 74 (office 10427) bookable under full-shard load.
--
-- V42 planted a short +6h window with only 5 internet seats. Parallel citizenview
-- shards drained those seats before Weiter. Replace that row with the same
-- CURTIME()-anchored pattern as V10 / V25: from the next 5-minute slot until 23:55
-- (or next day 00:05–23:55 when fewer than three hours remain — midnight break).
-- High seat count so captcha / reservation-expiry scenarios stay bookable.
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin.

DELETE FROM `oeffnungszeit`
WHERE `OeffnungszeitID` = 136216
   OR (`StandortID` = 74 AND `kommentar` LIKE 'ZMSKVR-1440%');

-- 5-minute Zeitschlitz. Stay on today until 23:55 while at least three hours
-- remain. A shorter stub is not enough for a bookable appointment, so the next
-- day is opened for the whole day, 00:05–23:55.
SET @slot_seconds := 300;
SET @latest_end := '23:55:00';
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / @slot_seconds) * @slot_seconds);

SET @start_sec := TIME_TO_SEC(@rounded_start);
SET @end_sec := TIME_TO_SEC(@latest_end);
SET @use_next_day := (@start_sec >= 24 * 3600) OR (@end_sec <= @start_sec) OR ((@end_sec - @start_sec) < 3 * 3600);

SET @appt_start := IF(@use_next_day, '00:05:00', @rounded_start);
SET @appt_end := @latest_end;

SET @range_start := IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 1 DAY), CURDATE());
SET @range_end :=
  IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY));

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
  @appt_start,
  '00:00:00',
  @appt_end,
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
