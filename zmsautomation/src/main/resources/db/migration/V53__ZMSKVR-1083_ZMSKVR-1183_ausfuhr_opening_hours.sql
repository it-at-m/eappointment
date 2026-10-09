-- ZMSKVR-1083 / ZMSKVR-1183: bookable day for KfZ Ausfuhr scope 70 (office 10416).
--
-- Scope 70 has slotsPerAppointment=8 and a 5-minute grid. Citizen API reserve /
-- preconfirm / confirm validation tests need at least one future internet seat
-- with multi-slot allowed so under-spa and over-spa slotCounts can be reserved
-- (or planted via hatFolgetermine after a valid reserve).
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin.

SET @slot_seconds := 300;
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
(136540, 70, @day, @day, 1, 0, 127, '00:00:00', @start, '00:00:00', @end, '00:05:00', 0, 3, 'ZMSKVR-1083 ZMSKVR-1183 Ausfuhr scope 70', 0, 1, 0, 0, NOW());
