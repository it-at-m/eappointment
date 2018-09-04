<?php

namespace BO\Zmsdb\Query;

class RequestProvider extends Base implements MappingInterface
{
    const TABLE = 'request_provider';

    const ALIAS = 'requestprovider';

    public function getEntityMapping()
    {
        return [
            'request__id' => 'requestprovider.request__id',
            'provider__id' => 'requestprovider.provider__id',
            'source' => 'requestprovider.source',
            'slots' => 'requestprovider.slots'
        ];
    }

    public function getReferenceMapping()
    {
        return [
            'request__$ref' => self::expression(
                'CONCAT("/request/", `requestprovider`.`source`, "/", `requestprovider`.`request__id`, "/")'
            ),
            'provider__$ref' => self::expression(
                'CONCAT("/provider/", `requestprovider`.`source`, "/", `requestprovider`.`provider__id`, "/")'
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
            'requestprovider.request__id',
            '=',
            'request.id'
        );
        return new Request($this, $this->getPrefixed('request__'));
    }

    public function addJoinProvider()
    {
        $this->leftJoin(
            new Alias(Provider::TABLE, 'provider'),
            'requestprovider.provider__id',
            '=',
            'provider.id'
        );
        return new Provider($this, $this->getPrefixed('provider__'));
    }

    public function addConditionRequestId($requestId)
    {
        $this->query->where('requestprovider.request__id', '=', $requestId);
        return $this;
    }

    public function addConditionProviderId($providerId)
    {
        $this->query->where('requestprovider.provider__id', '=', $providerId);
        return $this;
    }

    public function addConditionSource($sourceName)
    {
        $this->query->where('requestprovider.source', '=', $sourceName);
        return $this;
    }
}
