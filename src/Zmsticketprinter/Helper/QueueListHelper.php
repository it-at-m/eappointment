<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter\Helper;

use \BO\Mellon\Validator;

use \BO\Zmsentities\Collection\QueueList;

class QueueListHelper
{
    protected static $fullList = null;

    protected static $queueList = null;

    protected static $status = ['confirmed', 'queued', 'reserved', 'deleted'];

    protected static $missedStatus = ['missed'];

    public function __construct(\BO\Zmsentities\Scope $scope, $dateTime)
    {
        static::$fullList = static::createFullList($scope);
        static::$queueList = static::createQueueList($scope, $dateTime);
    }

    public static function getList()
    {
        return static::$queueList->withSortedArrival();
    }

    public static function getEstimatedWaitingTime()
    {
        return static::getList()->getLast()->waitingTimeEstimate;
    }

    protected static function createFullList($scope)
    {
        $fullList = \App::$http->readGetResult('/scope/'. $scope->getId() . '/queue/')->getCollection();
        return ($fullList->count()) ? $fullList : new QueueList();
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
