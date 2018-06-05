<?php

namespace BO\Zmsdb\Query;

/**
 * @SuppressWarnings(Public)
 */
class Apiquota extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'apiquota';

    public static function getQueryReadApiQuota()
    {
        return '
            SELECT * FROM `apiquota` WHERE `key` = :key AND `route` = :route
        ';
    }

    public static function getQueryReadApiQuotaList()
    {
        return '
            SELECT * FROM `apiquota` WHERE `key` = :key
        ';
    }

    public function getEntityMapping()
    {
        $mapping = [
            'key' => 'apiquota.key',
            'route' => 'apiquota.route',
            'period' => 'apiquota.period',
            'requests' => 'apiquota.requests'
        ];
        return $mapping;
    }

    public function addConditionApikey($apikey)
    {
        $this->query->where('apiquota.key', '=', $apikey);
        return $this;
    }

    public function addConditionRoute($route)
    {
        $this->query->where('apiquota.route', '=', $route);
        return $this;
    }
}
