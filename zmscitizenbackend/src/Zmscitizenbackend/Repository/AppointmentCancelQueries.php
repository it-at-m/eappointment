<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository;

class AppointmentCancelQueries
{
    public const string QUERY_LOCK_PROCESS = '
        SELECT
            process.BuergerID AS process_id,
            process.status AS status,
            process.absagecode AS auth_key,
            process.StandortID AS scope_id,
            process.IPTimeStamp AS create_timestamp
        FROM buerger AS process
        WHERE process.BuergerID = :processId
            AND (process.istFolgeterminvon IS NULL OR process.istFolgeterminvon = 0)
        FOR UPDATE
    ';

    public const string QUERY_BLOCK_PROCESS = '
        UPDATE buerger AS process
        SET
            process.Anmerkung = :amendment,
            process.custom_text_field = :customTextfield,
            process.custom_text_field2 = :customTextfield2,
            process.StandortID = 0,
            process.AbholortID = 0,
            process.Abholer = 0,
            process.Name = "dereferenced",
            process.IPadresse = "",
            process.IPTimeStamp = 0,
            process.NutzerID = 0,
            process.vorlaeufigeBuchung = 0,
            process.bestaetigt = 1,
            process.absagecode = "deref!0",
            process.EMail = "",
            process.priority = null,
            process.status = "blocked"
        WHERE
            (process.BuergerID = :processId AND process.absagecode = :authKey)
            OR process.istFolgeterminvon = :parentProcessId
    ';

    public const string QUERY_CANCEL_PROCESS = '
        UPDATE buerger AS process
        LEFT JOIN standort AS s USING (StandortID)
        SET
            process.Anmerkung = CONCAT(
                "Abgesagter Termin gebucht am: ",
                FROM_UNIXTIME(process.IPTimeStamp, "%d.%m.%Y, %H:%i"),
                " Uhr | ",
                IFNULL(process.Anmerkung, "")
            ),
            process.Name = "(abgesagt)",
            process.IPadresse = "",
            process.IPTimeStamp = :canceledTimestamp + (IFNULL(s.loeschdauer, 15) * 60),
            process.NutzerID = 0,
            process.vorlaeufigeBuchung = 1,
            process.status = "deleted",
            process.absagecode = :newAuthKey
        WHERE
            (process.BuergerID = :processId AND process.absagecode = :authKey)
            OR process.istFolgeterminvon = :parentProcessId
    ';

    public const string QUERY_CANCEL_OVERVIEW_CALENDAR = '
        UPDATE overview_calendar
        SET
            status = "cancelled",
            updated_at = CURRENT_TIMESTAMP
        WHERE process_id = :processId
            AND status <> "cancelled"
    ';
}
