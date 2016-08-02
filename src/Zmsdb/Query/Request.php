<?php

namespace BO\Zmsdb\Query;

class Request extends Base
{

    public static function getTablename()
    {
        $dbname_dldb = \BO\Zmsdb\Connection\Select::$dbname_dldb;
        return $dbname_dldb . '.dienstleistungen';
    }

    public static function getQuerySlots()
    {
        $dbname_dldb = \BO\Zmsdb\Connection\Select::$dbname_dldb;
        return 'SELECT
            x.`dienstleister` AS provider__id,
            x.`slots`
        FROM `' . $dbname_dldb . '`.`xdienst` x
            LEFT JOIN `' . $dbname_dldb . '`.`dienstleister` d ON x.dienstleister = d.id
        WHERE
            x.`dienstleistung` = :request_id
            AND x.`termin_hide` = 0
            AND d.`zms_termin` = 1
            ';
    }

    const QUERY_BY_PROCESSID = 'SELECT
            ba.`AnliegenID` AS id
        FROM `zmsbo`.`buergeranliegen` ba
        WHERE
            ba.`BuergerID` = :process_id
    ';

    public function getEntityMapping()
    {
        return [
            'id' => 'request.id',
            'link' => self::expression('CONCAT("https://service.berlin.de/dienstleistung/", `request`.`id`, "/")'),
            'name' => 'request.name',
            'source' => self::expression('"dldb"')
        ];
    }

    public function addConditionRequestId($requestId)
    {
        $this->query->where('id', '=', $requestId);
        return $this;
    }

    public function addConditionProcessId($processId)
    {
        $this->query->leftJoin(
            new Alias("buergeranliegen", 'buergeranliegen'),
            'buergeranliegen.AnliegenID',
            '=',
            'request.id'
        );
        $this->query->where('buergeranliegen.BuergerID', '=', $processId);
        return $this;
    }

    public function addConditionRequestCsv($requestCsv)
    {
        $requestIds = \explode(',', $requestCsv);
        foreach ($requestIds as $requestId) {
            $this->query->orWhere('id', '=', $requestId);
        }
        return $this;
    }

    public function addConditionProviderId($providerId)
    {
        $dbname_dldb = \BO\Zmsdb\Connection\Select::$dbname_dldb;
        $this->query->leftJoin(
            new Alias("$dbname_dldb.xdienst", 'xdienst'),
            'request.id',
            '=',
            'xdienst.dienstleistung'
        );
        $this->query->where('xdienst.dienstleister', '=', $providerId);
    }
}
