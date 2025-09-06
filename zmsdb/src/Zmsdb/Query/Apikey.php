<?php

namespace BO\Zmsdb\Query;

/**
 * @SuppressWarnings(Public)
 */
class Apikey extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'api_key';

    const QUOTATABLE = 'api_quota';

    protected function addRequiredJoins()
    {
        $this->leftJoin(
            new Alias('api_client', 'apiclientkey'),
            'apikey.apiClientID',
            '=',
            'apiclientkey.apiClientID'
        );
    }


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

    public function addConditionApikey($api_key)
    {
        $this->query->where('apikey.key', '=', $api_key);
        return $this;
    }
}
