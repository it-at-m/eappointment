<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

class ClusterHelper
{
    protected static $cluster = null;

    protected static $workstation = null;

    public function __construct(\BO\Zmsentities\Workstation $workstation)
    {
        static::$workstation = $workstation;
        static::$cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
    }

    public static function getEntity()
    {
        return static::$cluster;
    }

    public static function getScopeList()
    {
        return static::$workstation->getScopeList(static::$cluster);
    }

    public static function getProcessList($selectedDate)
    {
        if (static::isClusterEnabled()) {
            $processList = \App::$http
                ->readGetResult(
                    '/cluster/'. static::$cluster->id .'/process/'. $selectedDate .'/',
                    ['resolveReferences' => 1, 'gql' => GraphDefaults::getProcess()]
                );
        } else {
            $processList = \App::$http
                ->readGetResult(
                    '/scope/'. static::$workstation->scope['id'] .'/process/'. $selectedDate .'/',
                    ['resolveReferences' => 1, 'gql' => GraphDefaults::getProcess()]
                );
        }
        return ($processList) ? $processList->getCollection() : new \BO\Zmsentities\Collection\ProcessList();
    }

    public static function getNextProcess($excludedIds)
    {
        $queueList = static::getProcessList(\App::$now->format('Y-m-d'))
            ->toQueueList(\App::$now)
            ->withoutStatus(['fake','missed']);
        $excludedIds = (1 < $queueList->count()) ? $excludedIds : '';

        if (1 > $queueList->count()) {
            return new \BO\Zmsentities\Process();
        }
        if (static::isClusterEnabled()) {
            $nextProcess =  \App::$http->readGetResult(
                '/cluster/'. static::$cluster['id'] .'/queue/next/',
                [
                    'exclude' => $excludedIds,
                    'allowClusterWideCall' => \App::$allowClusterWideCall
                ]
            )->getEntity();
        } else {
            $nextProcess = \App::$http->readGetResult(
                '/scope/'. static::$workstation->scope['id'] .'/queue/next/',
                ['exclude' => $excludedIds]
            )->getEntity();
        }
        
        
        return ($nextProcess) ? $nextProcess : new \BO\Zmsentities\Process();
    }

    public static function isClusterEnabled()
    {
        return (static::$workstation->queue['clusterEnabled'] && static::$cluster);
    }
}
