<?php

namespace BO\Zmsdb\Query;

class RequestRelation extends Base implements MappingInterface
{
    const TABLE = 'request_relation';

    const ALIAS = 'request_relation';

    public function getEntityMapping()
    {
        return [
            'request__id' => self::TABLE .'.request__id',
            'provider__id' => self::TABLE .'.provider__id',
            'source' => self::TABLE .'.source',
            'slots' => self::TABLE .'.slots'
        ];
    }

    public function getReferenceMapping()
    {
        return [
            'request__$ref' => self::expression(
                'CONCAT("/request/", `'. self::TABLE .'`.`source`, "/", `'. self::TABLE .'`.`request__id`, "/")'
            ),
            'provider__$ref' => self::expression(
                'CONCAT("/provider/", `'. self::TABLE .'`.`source`, "/", `'. self::TABLE .'`.`provider__id`, "/")'
            )
        ];
    }

    public function addJoin()
    {
        return [
            $this->addJoinRequest(),
            $this->addJoinProvider(),
        ];
    }

    public function addJoinRequest()
    {
        $this->leftJoin(
            new Alias(Request::TABLE, 'request'),
            self::TABLE .'.request__id',
            '=',
            'request.id'
        );
        return new Request($this, $this->getPrefixed('request__'));
    }

    public function addJoinProvider()
    {
        $this->leftJoin(
            new Alias(Provider::TABLE, 'provider'),
            self::TABLE .'.provider__id',
            '=',
            'provider.id'
        );
        return new Provider($this, $this->getPrefixed('provider__'));
    }

    public function addConditionRequestId($requestId)
    {
        $this->query->where(self::TABLE .'.request__id', '=', $requestId);
        return $this;
    }

    public function addConditionProviderId($providerId)
    {
        $this->query->where(self::TABLE .'.provider__id', '=', $providerId);
        return $this;
    }

    public function addConditionSource($sourceName)
    {
        $this->query->where(self::TABLE .'.source', '=', $sourceName);
        return $this;
    }
}
