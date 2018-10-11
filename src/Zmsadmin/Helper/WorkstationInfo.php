<?php
/**
 *
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Mellon\Validator;

class WorkstationInfo
{
    public static function getInfoBoxData(\BO\Zmsentities\Workstation $workstation, $selectedDate)
    {
        $infoData = array('waitingTime' => 0, 'queueCount' => 0);
        $scope = new \BO\Zmsentities\Scope($workstation->scope);
        $dateTime = new \DateTimeImmutable($selectedDate);
        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = \App::$http->readGetResult('/scope/'. $scope->getId() .'/cluster/')->getEntity();
            $queueList = \App::$http->readGetResult(
                '/cluster/'. $cluster->getId() .'/process/'. $selectedDate .'/',
                ['resolveReferences' => 1]
            )->getCollection()->toQueueList($dateTime);
            $infoData['workstationList'] = static::getWorkstationsByCluster($cluster->getId());
        } else {
            $scope = \App::$http->readGetResult('/scope/'. $scope->getId() . '/')->getEntity();
            $queueList = \App::$http->readGetResult(
                '/scope/'. $scope->getId() .'/process/'. $selectedDate .'/',
                ['resolveReferences' => 0]
            )->getCollection()->toQueueList($dateTime);
            $infoData['workstationGhostCount'] = $scope->status['queue']['ghostWorkstationCount'];
            $infoData['workstationList'] = static::getWorkstationsByScope($scope->getId());
        }
        if ($queueList->count() > 0) {
            $infoData['waitingTime'] = $queueList->getLast()->waitingTimeEstimate;
            $infoData['queueCount'] = $queueList->withStatus(['confirmed', 'queued', 'reserved', 'deleted'])->count();
        }
        return $infoData;
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
