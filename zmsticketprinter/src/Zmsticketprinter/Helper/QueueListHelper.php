<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsticketprinter\Helper;

use BO\Mellon\Validator;
use BO\Zmsentities\Collection\QueueList;

class QueueListHelper
{
    protected static $fullList = null;

    protected static $queueList = null;

    /**
     * @var string[]
     *
     * @psalm-var list{'confirmed', 'queued', 'reserved', 'fake'}
     */
    protected static array $status = ['confirmed', 'queued', 'reserved', 'fake'];

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

    protected static function createFullList(\BO\Zmsentities\Scope $scope)
    {
        $fullList = \App::$http
            ->readGetResult('/scope/' . $scope->getId() . '/queue/')
            ->getCollection();
        return ($fullList) ? $fullList : new QueueList();
    }

    protected static function createQueueList(\BO\Zmsentities\Process|null $process): QueueList
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
