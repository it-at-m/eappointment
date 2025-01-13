<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsentities;

use BO\Zmsentities\Helper\DateTime;
use DateTimeInterface;

/**
 * @property int id
 * @property string name
 * @property string origin
 * @property string referenceType
 * @property string|null reference
 * @property string|null sessionid
 * @property array context
 * @property DateTimeInterface creationDateTime
 * @property DateTimeInterface expirationDateTime
 */
class EventLog extends Schema\Entity
{
    public const PRIMARY = 'id';

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

    public function getDefaults(): array
    {
        return [
            'id' => 0,
            'name' => '',
            'origin' => '',
            'referenceType' => 'none',
            'reference' => null,
            'sessionid' => null,
            'context' => [],
            'creationDateTime' => new DateTime(),
            'expirationDateTime' => new DateTime('9999-12-31 00:00:00'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function addData($mergeData): Schema\Entity
    {
        if (isset($mergeData['creationDateTime']) && is_string($mergeData['creationDateTime'])) {
            $mergeData['creationDateTime'] = new DateTime($mergeData['creationDateTime']);
        }
        if (isset($mergeData['expirationDateTime']) && is_string($mergeData['expirationDateTime'])) {
            $mergeData['expirationDateTime'] = new DateTime($mergeData['expirationDateTime']);
        }

        return parent::addData($mergeData);
    }

    public function jsonSerialize()
    {
        $clone = clone $this;
        if (isset($clone['creationDateTime']) && $clone['creationDateTime'] instanceof DateTimeInterface) {
            $clone['creationDateTime'] = $clone['creationDateTime']->format(DATE_ATOM);
        }
        if (isset($clone['expirationDateTime']) && $clone['expirationDateTime'] instanceof DateTimeInterface) {
            $clone['expirationDateTime'] = $clone['expirationDateTime']->format(DATE_ATOM);
        }

        return $clone->jsonSerialize();
    }

    /**
     * sets the expiration time by adding the desired seconds to live to the current time
     *
     * @param int $secondsToLive
     * @return void
     */
    public function setSecondsToLive(int $secondsToLive): void
    {
        $this['expirationDateTime'] = new DateTime('+' . $secondsToLive . ' seconds');
    }
}
