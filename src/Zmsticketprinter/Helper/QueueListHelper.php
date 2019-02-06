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

    protected static $status = ['confirmed', 'queued', 'reserved', 'deleted', 'fake'];

    public function __construct(\BO\Zmsentities\Scope $scope)
    {
        static::$fullList = static::createFullList($scope);
        static::$queueList = static::createQueueList();
    }

    public static function getList()
    {
        return static::$queueList;
    }

    public static function getEstimatedWaitingTime()
    {
        return static::getList()->getFakeOrLastWaitingnumber()->waitingTimeEstimate;
    }

    public static function getClientsBefore()
    {
        $entity = static::getList()->getFakeOrLastWaitingnumber();
        return (static::getList()->getQueuePositionByNumber($entity->number));
    }

    protected static function createFullList($scope)
    {
        $fullList = \App::$http
            ->readGetResult('/scope/'. $scope->getId() . '/queue/')
            ->getCollection();
        return ($fullList) ? $fullList : new QueueList();
    }

    protected static function createQueueList()
    {
        return (static::$fullList->count()) ? static::$fullList->withStatus(self::$status) : new QueueList();
    }
}
