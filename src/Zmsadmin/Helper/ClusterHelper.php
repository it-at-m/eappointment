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

    private static $workstation = null;

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
                    ['resolveReferences' => 1]
                )
                ->getCollection();
        } else {
            $processList = \App::$http
                ->readGetResult(
                    '/scope/'. static::$workstation->scope['id'] .'/process/'. $selectedDate .'/',
                    ['resolveReferences' => 1]
                )
                ->getCollection();
        }
        return ($processList) ? $processList : new \BO\Zmsentities\Collection\ProcessList();
    }

    public static function getNextProcess($excludedIds)
    {
        $hasQueue = static::getProcessList(\App::$now->format('Y-m-d'))->toQueueList(\App::$now)
            ->withoutStatus(['fake'])
            ->count();
        if (! $hasQueue) {
            return new \BO\Zmsentities\Process();
        }
        if (static::isClusterEnabled()) {
            return \App::$http
                ->readGetResult('/cluster/'. static::$cluster['id'] .'/queue/next/', ['exclude' => $excludedIds])
                ->getEntity();
        }
        return \App::$http->readGetResult(
            '/scope/'. static::$workstation->scope['id'] .'/queue/next/',
            ['exclude' => $excludedIds]
        )->getEntity();
    }

    public static function isClusterEnabled()
    {
        return (static::$workstation->queue['clusterEnabled'] && static::$cluster);
    }
}
