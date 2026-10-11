-- ZMSKVR-1493 / ZMSKVR-1531: a terminkunde is counted from the appointment minute.
--
-- V10 opens Gewerbeamt (Standort 2) only while three hours remain until 23:55.
-- Later the same day the calendar moves to tomorrow, so no slot can be booked
-- for the current minute. When today has no opening hours, keep one-minute
-- slots from the next minute through 23:59.
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin.

SET @minute_start := SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / 60) * 60);
SET @has_today := (
  SELECT COUNT(*) FROM `oeffnungszeit`
  WHERE `StandortID` = 2
    AND `Startdatum` <= CURDATE()
    AND `Endedatum` >= CURDATE()
);

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
SELECT
  136500,
  2,
  CURDATE(),
  CURDATE(),
  1,
  0,
  127,
  '00:00:00',
  @minute_start,
  '00:00:00',
  '23:59:00',
  '00:01:00',
  0,
  3,
  'ZMSKVR-1493 ZMSKVR-1531 late slot',
  0,
  1,
  0,
  30,
  NOW()
FROM DUAL
WHERE @has_today = 0
  AND TIME_TO_SEC(@minute_start) < TIME_TO_SEC('23:59:00');
