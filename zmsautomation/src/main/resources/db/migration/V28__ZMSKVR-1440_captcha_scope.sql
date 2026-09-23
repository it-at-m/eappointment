-- Flyway migration: captcha booking scope (ZMSKVR-1440)
--
-- Standort 74, Kommunale Verkehrsüberwachung (KVR-I/31), officeId 10427.
-- Not a Bürgerbüro, and no other ATAF feature books it.
--
-- Captcha JWT TTL is not stored per scope. The stack already uses
-- CAPTCHA_TOKEN_TTL=300 (5 minutes) from .devcontainer/.env.template.
-- V14 turns captcha off for every location; this migration turns it back on
-- for scope 74 only.
--
-- CURDATE()/CURTIME() follow zms-db TZ=Europe/Berlin (same calendar day as zms-web).

UPDATE `standort`
SET `reservierungsdauer` = 5,
    `captcha_activated_required` = 1
WHERE `StandortID` = 74;

UPDATE `preferences`
SET `value` = '5',
    `updateTimestamp` = NOW()
WHERE `entity` = 'scope'
  AND `id` = 74
  AND `groupName` = 'appointment'
  AND `name` = 'reservationDuration';

UPDATE `preferences`
SET `value` = '1',
    `updateTimestamp` = NOW()
WHERE `entity` = 'scope'
  AND `id` = 74
  AND `groupName` = 'client'
  AND `name` = 'captchaActivatedRequired';

-- round current time to next 5 minute slot
SET @rounded_start :=
  SEC_TO_TIME(CEILING(TIME_TO_SEC(CURTIME()) / 300) * 300);

-- desired end = +6 hours
SET @desired_end :=
  ADDTIME(@rounded_start, '06:00:00');

-- latest allowed end so slots still fit
SET @rounded_end :=
  LEAST(@desired_end, '23:55:00');

-- If the capped end is not after the rounded start (e.g. late night / 24:00:00 start),
-- use the next calendar day with appointment window 00:05–03:05 (still capped at 23:55).
SET @start_sec := TIME_TO_SEC(@rounded_start);
SET @end_sec := TIME_TO_SEC(@rounded_end);
SET @use_next_day := (@end_sec <= @start_sec);

SET @appt_start := IF(@use_next_day, '00:05:00', @rounded_start);
SET @appt_end :=
  IF(@use_next_day, LEAST(ADDTIME('00:05:00', '03:00:00'), '23:55:00'), @rounded_end);

SET @range_start := IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 1 DAY), CURDATE());
SET @range_end :=
  IF(@use_next_day, DATE_ADD(CURDATE(), INTERVAL 8 DAY), DATE_ADD(CURDATE(), INTERVAL 7 DAY));

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
  (136210, 74, @range_start, @range_end,
   1, 0, 127,
   '00:00:00', @appt_start,
   '00:00:00', @appt_end,
   '00:05:00',
   0, 5,
   'ZMSKVR-1440 Captcha Öffnungszeit',
   0, 5,
   0, 30,
   NOW());
