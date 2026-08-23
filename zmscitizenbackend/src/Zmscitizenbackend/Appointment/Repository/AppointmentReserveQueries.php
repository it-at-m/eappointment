<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Repository;

class AppointmentReserveQueries
{
    public const string QUERY_NEW_PROCESS_ID = '
        SELECT pseq.processId AS nextid
        FROM process_sequence pseq
        WHERE pseq.processId = (
            SELECT ps.processID
            FROM process_sequence ps
            LEFT JOIN buerger p ON ps.processId = p.BuergerID
            WHERE p.BuergerID IS NULL
            LIMIT %d, 1
        )
        FOR UPDATE
    ';

    public const string QUERY_INSERT_PROCESS = '
        INSERT INTO buerger (
            BuergerID,
            IPTimeStamp,
            absagecode,
            hatFolgetermine,
            istFolgeterminvon,
            displayNumber,
            wartenummer,
            StandortID,
            Datum,
            Uhrzeit,
            IPAdresse,
            Name,
            EMail,
            Anmerkung,
            custom_text_field,
            custom_text_field2,
            zustimmung_kundenbefragung,
            Erinnerungszeitpunkt,
            AnzahlPersonen,
            vorlaeufigeBuchung,
            aufruferfolgreich,
            status,
            parkedBy,
            wasMissed,
            priority,
            external_user_id
        ) VALUES (
            :processId,
            :createTimestamp,
            :authKey,
            :followUpCount,
            :parentProcessId,
            :displayNumber,
            :queueNumber,
            :scopeId,
            :appointmentDate,
            :appointmentTime,
            :createIp,
            :familyName,
            :email,
            :amendment,
            :customTextfield,
            :customTextfield2,
            :surveyAccepted,
            :reminderTimestamp,
            :clientCount,
            :isReserved,
            :isProcessing,
            :status,
            :parkedBy,
            :wasMissed,
            :priority,
            :externalUserId
        )
    ';

    public const string QUERY_INSERT_REQUEST = '
        INSERT INTO buergeranliegen (AnliegenID, source, BuergerID)
        VALUES (:requestId, :source, :processId)
    ';

    public const string QUERY_INSERT_SLOT_PROCESS = '
        REPLACE INTO slot_process
        SELECT
            s.slotID,
            b.BuergerID,
            NOW()
        FROM slot s
        INNER JOIN buerger b ON
            s.year = YEAR(b.Datum)
            AND s.month = MONTH(b.Datum)
            AND s.day = DAY(b.Datum)
            AND s.scopeID = b.StandortID
            AND s.status = "free"
            AND b.Uhrzeit BETWEEN s.time AND SEC_TO_TIME(TIME_TO_SEC(s.time) + (s.slotTimeInMinutes * 60) - 1)
        WHERE b.BuergerID = :processId
    ';

    public const string QUERY_SELECT_START_SLOT = '
        SELECT slotID, time, availabilityID, `public`
        FROM slot
        WHERE scopeID = :scopeId
            AND year = :year
            AND month = :month
            AND day = :day
            AND time = :time
            AND status = "free"
        LIMIT 1
    ';

    public const string QUERY_SELECT_SLOT_HIERARCHY = '
        SELECT s.slotID, s.time, h.ancestorLevel
        FROM slot_hiera h
        INNER JOIN slot s ON s.slotID = h.slotID
        WHERE h.ancestorID = :startSlotId
            AND h.ancestorLevel <= :slotCount
        ORDER BY h.ancestorLevel ASC
    ';
}
