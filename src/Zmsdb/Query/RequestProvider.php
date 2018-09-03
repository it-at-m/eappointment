<?php

namespace BO\Zmsdb\Query;

class RequestProvider extends Base
{
    const TABLE = 'request_provider';

    public static function getQuerySlotsByRequestId()
    {
        return '
            SELECT
                `provider__id`, `slots`
            FROM
                '. self::TABLE .'
            WHERE
                `request__id` = :request_id';
    }

    public static function getQuerySlotsByProviderId()
    {
        return 'SELECT
            `request__id`,
            `slots`
        FROM `request_provider`
        WHERE
            `provider__id` = :provider_id
            ';
    }

    public static function getQueryRequestSlotCount()
    {
        return '
            SELECT
                `slots`
                FROM
                    '. self::TABLE .'
                WHERE
                    `request__id` = :request_id AND
                    `provider__id` = :provider_id
        ';
    }
}
