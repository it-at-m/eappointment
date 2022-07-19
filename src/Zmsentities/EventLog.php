<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsentities;

use BO\Zmsentities\Helper\DateTime;

class EventLog extends Schema\Entity
{
    public const PRIMARY = 'eventId';

    public static $schema = "eventlog.json";

    public const LIVETIME_LONGER  = 315360000; // 10 years
    public const LIVETIME_LONG    = 94608000; // 3 years
    public const LIVETIME_YEAR    = 31536000; // one year (365 days)
    public const LIVETIME_DEFAULT = 15552000; // half a year (180 days)
    public const LIVETIME_MONTH   = 2592000; // one month (30 days)
    public const LIVETIME_WEEK    = 604800; // one week
    public const LIVETIME_DAY     = 86400; // one day
    public const LIVETIME_HOUR    = 3600; // one hour

    /***************  Event Names ****************/
    public const CLIENT_PROCESSLIST_REQUEST = 'CLIENT_PROCESSLIST_REQUEST';
    public const CLIENT_PROCESSLIST_SEND = 'CLIENT_PROCESSLIST_REQUEST';
    // examples for future use below
    public const QUEUE_PROCESS_SCHEDULE = 'CLIENT_PROCESSLIST_REQUEST';
    public const QUEUE_PROCESS_DELETE = 'CLIENT_PROCESSLIST_REQUEST';
    public const WORKSTATION_PROCESS_CALL = 'CLIENT_PROCESSLIST_REQUEST';
    public const WORKSTATION_PROCESS_START = 'CLIENT_PROCESSLIST_REQUEST';
    public const WORKSTATION_PROCESS_FINISH = 'CLIENT_PROCESSLIST_REQUEST';

    public function getDefaults()
    {
        return [
            'id' => 0,
            'name' => '',
            'origin' => '',
            'referenceType' => 'none',
            'reference' => null,
            'sessionid' => null,
            'context' => '{}',
            'creationTimestamp' => new DateTime(),
            'expirationTimestamp' => new DateTime('9999-12-31 00:00:00'),
        ];
    }
}