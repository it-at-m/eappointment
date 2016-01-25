<?php

namespace BO\Zmsdb\Query;

class Request extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'startinfo.dienstleistungen';

    public function getEntityMapping()
    {
        return [
            'id' => 'request.id',
            'link' => self::expression('CONCAT("https://service.berlin.de/dienstleistung/", `request`.`id`, "/")'),
            'name' => 'request.name',
            'source' => self::expression('"dldb"'),
        ];
    }

    public function addConditionRequestId($requestId)
    {
        $this->query->where('id', '=', $requestId);
        return $this;
    }
}
