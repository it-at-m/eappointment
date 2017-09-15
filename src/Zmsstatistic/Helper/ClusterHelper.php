<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Helper;

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

    public static function getRequestList()
    {
        if (static::isClusterEnabled()) {
            $requestList = \App::$http
                ->readGetResult('/cluster/'. static::$cluster->id .'/request/')
                ->getCollection();
        } else {
            $requestList = \App::$http
                ->readGetResult('/scope/'. static::$workstation->scope['id'] .'/request/')
                ->getCollection();
        }
        return $requestList;
    }

    public static function getProcessList($selectedDate)
    {
        if (static::isClusterEnabled()) {
            $processList = \App::$http
                ->readGetResult(
                    '/cluster/'. static::$cluster->id .'/process/'. $selectedDate .'/',
                    ['resolveReferences' => 0]
                )
                ->getCollection();
        } else {
            $processList = \App::$http
                ->readGetResult(
                    '/scope/'. static::$workstation->scope['id'] .'/process/'. $selectedDate .'/',
                    ['resolveReferences' => 0]
                )
                ->getCollection();
        }
        return $processList;
    }

    public static function getNextProcess($excludedIds)
    {
        if (static::isClusterEnabled()) {
            $process = \App::$http
                ->readGetResult('/cluster/'. static::$cluster['id'] .'/queue/next/', ['exclude' => $excludedIds])
                ->getEntity();
        } else {
            $process = \App::$http
                ->readGetResult(
                    '/scope/'. static::$workstation->scope['id'] .'/queue/next/',
                    ['exclude' => $excludedIds]
                )
                ->getEntity();
        }
        return $process;
    }

    public static function getPreferedScopeByCluster()
    {
        $scope = new \BO\Zmsentities\Scope(static::$workstation->scope);
        if (static::isClusterEnabled()) {
            try {
                $scope = \App::$http
                    ->readGetResult('/scope/prefered/cluster/'. static::$cluster['id'] .'/', ['resolveReferences' => 0])
                    ->getEntity();
            } catch (\BO\Zmsclient\Exception $exception) {
            }
        }
        return $scope;
    }

    protected static function isClusterEnabled()
    {
        return (static::$workstation->queue['clusterEnabled'] && static::$cluster);
    }
}
