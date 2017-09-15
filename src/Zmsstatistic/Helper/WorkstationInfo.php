<?php
/**
 *
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Helper;

use \BO\Mellon\Validator;

class WorkstationInfo
{
    public static function getInfoBoxData(\BO\Zmsentities\Workstation $workstation, $dateString = '')
    {
        $infoData = array();
        $scope = new \BO\Zmsentities\Scope($workstation->scope);
        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = \App::$http->readGetResult('/scope/'. $scope->id .'/cluster/')->getEntity();
            $queueList = \App::$http->readGetResult('/cluster/'. $cluster->id . '/queue/')->getCollection();
            $infoData['workstationList'] = static::getWorkstationsByCluster($cluster->id);
        } else {
            $dateTime = new \BO\Zmsentities\Helper\DateTime($dateString);
            $scope = \App::$http->readGetResult('/scope/'. $scope->id . '/')->getEntity();
            $queueList =  \App::$http->readGetResult('/scope/'. $scope->id . '/queue/')->getCollection();
            $infoData['workstationGhostCount'] = $scope->status['queue']['ghostWorkstationCount'];
            $infoData['availabilities'] = static::getAvailabilityByScopeAndDateTime($scope->id, $dateTime);
            $infoData['workstationList'] = static::getWorkstationsByScope($scope->id);
        }
        if ($queueList) {
            $infoData['waitingTime'] = $queueList->getLast()->waitingTimeEstimate;
            $infoData['queueCount'] = $queueList->count();
        }
        return $infoData;
    }

    public static function getAvailabilityByScopeAndDateTime($scopeId, $dateTime)
    {
        $collection = \App::$http
            ->readGetResult('/scope/'. $scopeId . '/availability/')
            ->getCollection();
        return ($collection) ? $collection->withDateTime($dateTime)->getArrayCopy() : [];
    }

    public static function getWorkstationsByScope($scopeId)
    {
        return \App::$http
            ->readGetResult('/scope/'. $scopeId . '/workstation/', ['resolveReferences' => 1])
            ->getCollection();
    }

    public static function getWorkstationsByCluster($clusterId)
    {
        return \App::$http
            ->readGetResult('/cluster/'. $clusterId . '/workstation/', ['resolveReferences' => 1])
            ->getCollection();
    }
}
