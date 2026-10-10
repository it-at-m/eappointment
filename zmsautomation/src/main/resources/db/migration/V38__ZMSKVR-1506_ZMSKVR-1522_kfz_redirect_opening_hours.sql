-- ZMSKVR-1506 / ZMSKVR-1522: a forwarded appointment keeps priority Mittel.
--
-- KfZ Zulassungsstelle Briefbüro (Standort 46) and Import (Standort 49).
-- No other Flyway migration gives these scopes opening hours, so a Terminkunde
-- cannot be booked there. One 15-minute window on the source is enough.
-- The target gets the same window so the redirect stays inside an open calendar.
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin.

SET @slot_seconds := 900;
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / @slot_seconds) * @slot_seconds);
SET @use_next_day := TIME_TO_SEC(@rounded_start) >= TIME_TO_SEC('23:30:00');
SET @day := IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 1 DAY), CURDATE());
SET @start := IF(@use_next_day, '09:00:00', @rounded_start);
SET @end := IF(@use_next_day, '11:00:00', '23:45:00');

INSERT IGNORE INTO `oeffnungszeit`
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
(136458, 46, @day, @day, 1, 0, 127, '00:00:00', @start, '00:00:00', @end, '00:15:00', 0, 1, 'ZMSKVR-1506 ZMSKVR-1522 Briefbüro', 0, 1, 0, 0, NOW()),
(136459, 49, @day, @day, 1, 0, 127, '00:00:00', @start, '00:00:00', @end, '00:15:00', 0, 1, 'ZMSKVR-1506 ZMSKVR-1522 Import', 0, 1, 0, 0, NOW());
