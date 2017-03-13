<?php

namespace BO\Zmsdb\Query;

class Request extends Base
{
    const TABLE = 'request';

    public static function getQuerySlots()
    {
        return 'SELECT
            `provider__id`,
            `slots`
        FROM request_provider
        WHERE
            `request__id` = :request_id
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
        $mapping = [
            'id' => 'request.id',
            'link' => 'request.link',
            'name' => 'request.name',
            'group' => 'request.group',
            'source' => 'request.source',
        ];
        if ($this->getResolveLevel() > 0) {
            $mapping['data'] = 'request.data';
        }
        return $mapping;
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

    public function addConditionProviderId($providerId)
    {
        $this->query->leftJoin(
            new Alias("request_provider", 'xrequest'),
            'request.id',
            '=',
            'xrequest.request__id'
        );
        $this->query->where('xrequest.provider__id', '=', $providerId);
    }

    public function postProcess($data)
    {
        if (isset($data['data']) && $data['data']) {
            $data['data'] = json_decode($data['data'], true);
        }
        return $data;
    }
}
