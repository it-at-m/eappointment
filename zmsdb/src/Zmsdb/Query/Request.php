<?php

namespace BO\Zmsdb\Query;

class Request extends Base
{
    const TABLE = 'request';

    const BATABLE = 'buergeranliegen';

    const QUERY_BY_PROCESSID = 'SELECT
            ba.`AnliegenID` AS id
        FROM `buergeranliegen` ba
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
            'parent_id' => 'request.parent_id',
            'variant_id' => 'request.variant_id',
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
            self::expression('
                buergeranliegen.AnliegenID = request.id
                AND FIND_IN_SET(request.source, REPLACE(buergeranliegen.source, ";", ",")) > 0
            ')
        );
        $this->query->where('buergeranliegen.BuergerID', '=', $processId);
        return $this;
    }

    public function addConditionArchiveId($archiveId)
    {
        $this->leftJoin(
            new Alias("buergeranliegen", 'buergeranliegen'),
            self::expression('
                buergeranliegen.AnliegenID = request.id
                AND FIND_IN_SET(request.source, REPLACE(buergeranliegen.source, ";", ",")) > 0
            ')
        );
        $this->query->where('buergeranliegen.BuergerarchivID', '=', $archiveId);
        return $this;
    }

    public function addConditionProvider($providerId, $source)
    {
        $this->leftJoin(
            new Alias("request_provider", 'xrequest'),
            'request.id',
            '=',
            'xrequest.request__id'
        );
        $this->query->where(function (\BO\Zmsdb\Query\Builder\ConditionBuilder $query) use ($providerId, $source) {
            $query->andWith('xrequest.provider__id', '=', $providerId);
            $query->andWith('xrequest.source', '=', $source);
            $query->andWith('xrequest.bookable', '=', 1);
        });
        return $this;
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

    public function addConditionIds($ids)
    {
        $this->query->where(function (\BO\Zmsdb\Query\Builder\ConditionBuilder $query) use ($ids) {
            foreach ($ids as $id) {
                $query->orWith('request.id', '=', $id);
            }
        });

        return $this;
    }

    public function getQueryCountInBuergeranliegen(): string
    {
        return '
        SELECT COUNT(*)
        FROM ' . self::BATABLE . '
        WHERE AnliegenID = :request_id
    ';
    }
}
