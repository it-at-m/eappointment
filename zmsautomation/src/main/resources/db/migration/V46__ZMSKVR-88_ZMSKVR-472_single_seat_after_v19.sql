-- Flyway migration: one internet seat on the day after V19's Ruppertstraße range (ZMSKVR-88 / ZMSKVR-472)
--
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin (same calendar day as zms-web).
--
-- V19 opens Passkalender scopes 172/184/342 (office 10502) for CURDATE()..+7 (or +8 when
-- today has less than three hours left) with Anzahlterminarbeitsplaetze = 5 each, so each
-- UI grid time still has several free seats behind it. The slot-taken callout race needs
-- exactly one internet Terminarbeitsplatz, so this migration adds a single day after that
-- range on Standort 172 only.
--
-- Standorte (see V5 / V19):
--  - 172 -> officeId 10502 (WB04 Pass, KVR-II/221)

-- Same window math as V19__ZMSKVR-1124_opening_hours_for_ruppertstasse.sql
SET @slot_seconds := 300;
SET @latest_end := '23:55:00';
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / @slot_seconds) * @slot_seconds);

SET @start_sec := TIME_TO_SEC(@rounded_start);
SET @end_sec := TIME_TO_SEC(@latest_end);
SET @use_next_day := (@start_sec >= 24 * 3600) OR (@end_sec <= @start_sec) OR ((@end_sec - @start_sec) < 3 * 3600);

SET @v19_range_end :=
  IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY));
SET @single_seat_day := DATE_ADD(@v19_range_end, INTERVAL 1 DAY);

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
  -- Bürgerbüro Ruppertstraße (KVR-II/221) – WB04 Pass (officeId 10502), one internet seat
  (136510, 172, @single_seat_day, @single_seat_day,
   1, 0, 127,
   '00:00:00', '08:00:00',
   '00:00:00', '18:00:00',
   '00:05:00',
   0, 1,
   'ZMSKVR-88 ZMSKVR-472 single internet seat after V19 range',
   0, 5,
   0, 30,
   NOW());
