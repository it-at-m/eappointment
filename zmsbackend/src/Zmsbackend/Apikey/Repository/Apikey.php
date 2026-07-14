<?php

namespace BO\Zmsbackend\Apikey\Repository;

/**
 * @SuppressWarnings(Public)
 */
class Apikey extends \BO\Zmsbackend\Query\Base implements \BO\Zmsbackend\Query\MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'apikey';

    const QUOTATABLE = 'apiquota';

    #[\Override]
    protected function addRequiredJoins()
    {
        $this->leftJoin(
            new \BO\Zmsbackend\Query\Alias('apiclient', 'apiclientkey'),
            'apikey.apiClientID',
            '=',
            'apiclientkey.apiClientID'
        );
    }


    #[\Override]
    public function getEntityMapping()
    {
        $mapping = [
            'key' => 'apikey.key',
            'createIP' => 'apikey.createIP',
            'apiclient__clientKey' => 'apiclientkey.clientKey',
            'apiclient__accesslevel' => 'apiclientkey.accesslevel',
            'ts' => 'apikey.ts'
        ];
        return $mapping;
    }

    public function addConditionApikey($apikey)
    {
        $this->query->where('apikey.key', '=', $apikey);
        return $this;
    }
}
