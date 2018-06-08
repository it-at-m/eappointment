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
    const TABLE = 'apikey';

    const QUOTATABLE = 'apiquota';

    public function getEntityMapping()
    {
        $mapping = [
            'key' => 'apikey.key',
            'createIP' => 'apikey.createIP',
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
