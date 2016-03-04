<?php

namespace BO\Zmsdb\Query;

class Request extends Base
{

    /**
     *
     * @var String TABLE mysql table reference
     */
    const TABLE = 'startinfo.dienstleistungen';

    const QUERY_SLOTS = 'SELECT
            x.`dienstleister` AS provider__id,
            x.`slots`
        FROM `startinfo`.`xdienst` x
            LEFT JOIN `startinfo`.`dienstleister` d ON x.dienstleister = d.id
        WHERE
            x.`dienstleistung` = :request_id
            AND x.`termin_hide` = 0
            AND d.`zms_termin` = 1
    ';

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

    public function addConditionRequestCsv($requestCsv)
    {
        $requestIds = \explode(',', $requestCsv);
        foreach ($requestIds as $requestId) {
            $this->query->orWhere('id', '=', $requestId);
        }
        return $this;
    }
}
