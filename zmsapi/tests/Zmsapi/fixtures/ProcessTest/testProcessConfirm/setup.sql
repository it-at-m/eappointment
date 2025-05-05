DELETE FROM gesamtkalender
WHERE scope_id = 380 AND time BETWEEN '2016-05-27 09:30:00' AND '2016-05-27 09:35:00';

-- Wir brauchen nur 1 Seat (=intern‑Count 1) und 2 Slots à 5 min
INSERT INTO gesamtkalender (scope_id, time, seat, status)
VALUES
    (380, '2016-05-27 09:30:00', 1, 'free'),
    (380, '2016-05-27 09:35:00', 1, 'free');
