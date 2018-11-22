<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Mellon\Validator;

use \BO\Zmsentities\Collection\QueueList;

class QueueListHelper
{
    protected static $fullList = null;

    protected static $queueList = null;

    protected static $status = ['confirmed', 'queued', 'reserved', 'deleted'];

    protected static $missedStatus = ['missed'];

    public function __construct(ClusterHelper $clusterHelper, \BO\Zmsentities\Scope $scope, $selectedDate)
    {
        $dateTime = (new \DateTimeImmutable($selectedDate))->modify(\App::$now->format('H:i'));
        static::$fullList = static::createFullList($clusterHelper, $dateTime);
        static::$queueList = static::createQueueList($scope, $dateTime);
    }

    public static function getList()
    {
        return static::$queueList;
    }

    public static function getMissedList()
    {
        return  static::$fullList->withStatus(self::$missedStatus);
    }

    protected static function createFullList($clusterHelper, $dateTime)
    {
        $fullList = $clusterHelper->getProcessList($dateTime->format('Y-m-d'));
        return ($fullList->count()) ? $fullList->toQueueList($dateTime) : new QueueList();
    }

    protected static function createQueueList($scope, $dateTime)
    {
        return (static::$fullList->count()) ?
            static::$fullList
                ->withStatus(self::$status)
                ->withEstimatedWaitingTime(
                    $scope->getPreference('queue', 'processingTimeAverage'),
                    $scope->getCalculatedWorkstationCount(),
                    $dateTime,
                    false
                ) :
            new QueueList();
    }
}
