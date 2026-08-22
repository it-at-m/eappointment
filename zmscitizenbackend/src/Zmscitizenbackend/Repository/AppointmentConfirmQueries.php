<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository;

class AppointmentConfirmQueries
{
    public const string QUERY_LOCK_PROCESS = '
        SELECT
            process.BuergerID AS process_id,
            process.status AS status
        FROM buerger AS process
        WHERE process.BuergerID = :processId
            AND (process.istFolgeterminvon IS NULL OR process.istFolgeterminvon = 0)
        FOR UPDATE
    ';

    public const string QUERY_CONFIRM_PROCESS = '
        UPDATE buerger
        SET
            status = "confirmed",
            bestaetigt = 1,
            vorlaeufigeBuchung = 0
        WHERE BuergerID = :processId
            AND (istFolgeterminvon IS NULL OR istFolgeterminvon = 0)
    ';

    public const string QUERY_UPDATE_FOLLOWING_PROCESS = '
        UPDATE buerger
        SET vorlaeufigeBuchung = 0
        WHERE istFolgeterminvon = :processId
    ';
}
