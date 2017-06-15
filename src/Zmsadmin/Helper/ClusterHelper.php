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
        if (1 == $workstation->queue['clusterEnabled']) {
            static::$cluster = \App::$http
                ->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
        }
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
        if (static::$cluster) {
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
        if (static::$cluster) {
            $processList = \App::$http
                ->readGetResult('/cluster/'. static::$cluster->id .'/process/'. $selectedDate .'/')
                ->getCollection();
        } else {
            $processList = \App::$http
                ->readGetResult('/scope/'. static::$workstation->scope['id'] .'/process/'. $selectedDate .'/')
                ->getCollection();
        }
        return $processList;
    }
}
