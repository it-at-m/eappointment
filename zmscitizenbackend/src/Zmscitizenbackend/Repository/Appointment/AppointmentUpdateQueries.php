<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository\Appointment;

class AppointmentUpdateQueries
{
    public const string QUERY_LOCK_PROCESS = '
        SELECT
            process.BuergerID AS process_id,
            process.external_user_id AS external_user_id
        FROM buerger AS process
        WHERE process.BuergerID = :processId
            AND (process.istFolgeterminvon IS NULL OR process.istFolgeterminvon = 0)
        FOR UPDATE
    ';

    public const string QUERY_UPDATE_CLIENT_DATA = '
        UPDATE buerger
        SET
            Name = :familyName,
            EMail = :email,
            telefonnummer_fuer_rueckfragen = :telephone,
            Telefonnummer = :telephone,
            custom_text_field = :customTextfield,
            custom_text_field2 = :customTextfield2,
            external_user_id = :externalUserId
        WHERE BuergerID = :processId
            AND (istFolgeterminvon IS NULL OR istFolgeterminvon = 0)
    ';

    public const string QUERY_SELECT_PROCESSES_BY_MAIL = '
        SELECT
            process.BuergerID AS process_id,
            CASE
                WHEN process.status = "called"
                    AND process.aufrufzeit != "00:00:00"
                    AND process.NutzerID != 0
                    AND process.AbholortID = 0
                    THEN "called"
                WHEN process.status = "called" AND process.Uhrzeit = "00:00:00"
                    THEN "queued"
                WHEN process.status = "called"
                    AND process.vorlaeufigeBuchung = 0
                    AND process.bestaetigt = 1
                    THEN "confirmed"
                ELSE process.status
            END AS status
        FROM buerger AS process
        WHERE process.EMail = :email
            AND (process.StandortID = :scopeId OR process.AbholortID = :scopeId)
            AND (process.istFolgeterminvon IS NULL OR process.istFolgeterminvon = 0)
    ';
}
