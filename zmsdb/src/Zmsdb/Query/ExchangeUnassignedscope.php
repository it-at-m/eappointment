<?php

namespace BO\Zmsdb\Query;

class ExchangeUnassignedscope extends Base
{
    const QUERY_READ_REPORT = "SELECT 
            scope.StandortID, 
            scope.Bezeichnung, 
            IFNULL(COUNT(citizen.BuergerID), '') AS TerminAnzahl, 
            IFNULL(GROUP_CONCAT(DISTINCT citizen.Datum ORDER BY citizen.Datum), '') AS TerminDaten 
        FROM scope 
        LEFT JOIN provider 
            ON scope.InfoDienstleisterID = provider.id 
        LEFT JOIN citizen 
            ON citizen.StandortID = scope.StandortID
        WHERE provider.id IS NULL 
        GROUP BY scope.StandortID;
    ";
}
