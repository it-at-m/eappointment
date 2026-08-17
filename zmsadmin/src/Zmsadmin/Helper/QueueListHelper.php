<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin\Helper;

use BO\Mellon\Validator;
use BO\Zmsentities\Collection\QueueList;

class QueueListHelper
{
    protected static $fullList = null;

    protected static $queueList = null;

    protected static $status = ['preconfirmed', 'confirmed', 'queued', 'reserved', 'deleted', 'fake'];

    protected static $missedStatus = ['missed'];

    protected static $parkedStatus = ['parked'];

    public function __construct(ClusterHelper $clusterHelper, $selectedDate)
    {
        $dateTime = (new \DateTimeImmutable($selectedDate))->modify(\App::$now->format('H:i:s'));
        static::$fullList = static::createFullList($clusterHelper, $dateTime);
        static::$queueList = static::createQueueList();
    }

    public static function getList()
    {
        return static::$queueList;
    }

    public static function getFullList()
    {
        return static::$fullList;
    }

    /** @psalm-api */
    public static function getEstimatedWaitingTime()
    {
        return self::getList()->getFakeOrLastWaitingnumber()->waitingTimeEstimate;
    }

    /** @psalm-api */
    public static function getOptimisticWaitingTime()
    {
        return self::getList()->getFakeOrLastWaitingnumber()->waitingTimeOptimistic;
    }

    public static function getWaitingCount()
    {
        // return count -1 because of faked entry
        return (self::getList()->count()) ? (self::getList()->withoutStatus(['fake'])->count()) : 0;
    }

    /** @psalm-api */
    public static function getWaitingClientsEffective()
    {
        $effectiveStatus = self::$status;
        unset($effectiveStatus['fake']);

        return self::getList()
            ->withoutStatus(['fake'])
            ->getCountWithWaitingTime(\App::$now)
            ->count();
    }

    /** @psalm-api */
    public static function getWaitingClientsBeforeNext()
    {
        $entity = self::getList()->getFakeOrLastWaitingnumber();
        return (self::getList()->getQueuePositionByNumber($entity->number));
    }

    protected static function createFullList(ClusterHelper $clusterHelper, \DateTimeImmutable|false $dateTime)
    {
        $fullList = $clusterHelper->getProcessList($dateTime->format('Y-m-d'));
        return ($fullList->count()) ? $fullList->toQueueList($dateTime) : new QueueList();
    }

    protected static function createQueueList()
    {
        return (static::$fullList->count()) ?
            static::$fullList->withStatus(self::$status) :
            new QueueList();
    }
}
