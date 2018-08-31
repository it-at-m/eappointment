<?php

namespace BO\Zmsdb\Query;

class Request extends Base
{
    const TABLE = 'request';

    const BATABLE = 'buergeranliegen';

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

    public static function getQueryRequestSlotCount()
    {
        return 'SELECT
            `slots`
        FROM request_provider
        WHERE
            `request__id` = :request_id AND
            `provider__id` = :provider_id
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
            'source' => 'request.source'
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
        $this->leftJoin(
            new Alias("buergeranliegen", 'buergeranliegen'),
            'buergeranliegen.AnliegenID',
            '=',
            'request.id'
        );
        $this->query->where('buergeranliegen.BuergerID', '=', $processId);
        return $this;
    }

    public function addConditionArchiveId($archiveId)
    {
        $this->leftJoin(
            new Alias("buergeranliegen", 'buergeranliegen'),
            'buergeranliegen.AnliegenID',
            '=',
            'request.id'
        );
        $this->query->where('buergeranliegen.BuergerarchivID', '=', $archiveId);
        return $this;
    }

    public function addConditionProviderId($providerId)
    {
        $this->leftJoin(
            new Alias("request_provider", 'xrequest'),
            'request.id',
            '=',
            'xrequest.request__id'
        );
        $this->query->where('xrequest.provider__id', '=', $providerId);
    }

    public function addConditionRequestSource($source)
    {
        $this->query->where('request.source', '=', $source);
        return $this;
    }

    public function postProcess($data)
    {
        if (isset($data[$this->getPrefixed('data')]) && $data[$this->getPrefixed('data')]) {
            $data[$this->getPrefixed('data')] = json_decode($data[$this->getPrefixed('data')], true);
        }
        return $data;
    }
}
