<?php

namespace BO\Zmsdb\Query;

class ExchangeUnassignedscope extends Base
{
    const QUERY_READ_REPORT = "SELECT 
            standort.StandortID, 
            standort.Bezeichnung, 
            IFNULL(COUNT(buerger.BuergerID), '') AS TerminAnzahl, 
            IFNULL(GROUP_CONCAT(DISTINCT buerger.Datum ORDER BY buerger.Datum), '') AS TerminDaten 
        FROM standort 
        LEFT JOIN provider 
            ON standort.InfoDienstleisterID = provider.id 
        LEFT JOIN buerger 
            ON buerger.StandortID = standort.StandortID
        WHERE provider.id IS NULL 
        GROUP BY standort.StandortID;
    ";
}
