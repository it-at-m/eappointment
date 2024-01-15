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

    protected static $status = ['confirmed', 'queued', 'reserved', 'fake'];

    public function __construct(\BO\Zmsentities\Scope $scope, \BO\Zmsentities\Process $process = null)
    {
        static::$fullList = static::createFullList($scope);
        static::$queueList = static::createQueueList($process);
    }

    public static function getList()
    {
        return static::$queueList;
    }

    public static function getEstimatedWaitingTime()
    {
        return static::getList()->getFakeOrLastWaitingnumber()->waitingTimeEstimate;
    }

    public static function getOptimisticWaitingTime()
    {
        return static::getList()->getFakeOrLastWaitingnumber()->waitingTimeOptimistic;
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

    protected static function createQueueList($process)
    {
        $queueList = new QueueList();
        if (static::$fullList->count()) {
            foreach (static::$fullList->withStatus(self::$status) as $entity) {
                if (! $process || $entity->number != $process->queue->number) {
                    $queueList->addEntity($entity);
                }
            }
        }
        return $queueList;
    }
}
