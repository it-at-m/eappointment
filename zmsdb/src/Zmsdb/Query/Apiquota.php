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

    public static function getQueryReadApiQuotaListByKey()
    {
        return '
            SELECT * FROM `apiquota` WHERE `key` = :key
        ';
    }

    public static function getQueryReadApiQuotaExpired($dateTime)
    {
        $timeStamp = $dateTime->getTimestamp();
        return '
        SELECT * FROM apiquota WHERE ts <= (CASE period
            WHEN "month" THEN UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(' . $timeStamp . '), INTERVAL -1 MONTH))
            WHEN "week" THEN UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(' . $timeStamp . '), INTERVAL -1 WEEK))
            WHEN "day" THEN UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(' . $timeStamp . '), INTERVAL -1 DAY))
            WHEN "hour" THEN UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(' . $timeStamp . '), INTERVAL -1 HOUR))
            WHEN "minute" THEN UNIX_TIMESTAMP(DATE_ADD(FROM_UNIXTIME(' . $timeStamp . '), INTERVAL -1 MINUTE))
        END)
        ';
    }

    public function getEntityMapping()
    {
        $mapping = [
            'key' => 'apiquota.key',
            'route' => 'apiquota.route',
            'period' => 'apiquota.period',
            'requests' => 'apiquota.requests',
            'ts' => 'apiquota.ts'
        ];
        return $mapping;
    }

    public function addConditionQuotaId($quotaId)
    {
        $this->query->where('apiquota.quotaid', '=', $quotaId);
        return $this;
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
