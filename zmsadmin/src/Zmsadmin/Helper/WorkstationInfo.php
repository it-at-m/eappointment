<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Mellon\Validator;

class WorkstationInfo
{
    public static function getInfoBoxData(\BO\Zmsentities\Workstation $workstation, $selectedDate)
    {
        $infoData = array(
            'waitingTimeEstimate' => 0,
            'waitingTimeOptimistic' => 0,
            'waitingClientsFullList' => 0,
            'waitingClientsBeforeNext' => 0,
            'waitingClientsEffective' => 0
        );
        $scope = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/workstationcount/')->getEntity();

        $clusterHelper = (new ClusterHelper($workstation));
       
        $infoData['workstationGhostCount'] = $scope->status['queue']['ghostWorkstationCount'];
        $infoData['workstationList'] = ($clusterHelper->isClusterEnabled()) ?
            static::getWorkstationsByCluster($clusterHelper->getEntity()->getId()) :
            static::getWorkstationsByScope($scope->getId());

        $queueListHelper = (new QueueListHelper($clusterHelper, $selectedDate));
        
        $infoData['countCurrentlyProcessing'] = count($queueListHelper->getFullList()->withStatus(['called', 'processing']));

        if ($queueListHelper->getWaitingCount()) {
            $infoData['waitingClientsFullList'] = $queueListHelper->getWaitingCount();
            if ($selectedDate == \App::$now->format('Y-m-d')) {
                $infoData = static::getAdditionalInfoData($infoData, $queueListHelper);
            }
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

    protected static function getAdditionalInfoData($infoData, $queueListHelper)
    {
        $infoData['waitingTimeEstimate'] = $queueListHelper->getEstimatedWaitingTime();
        $infoData['waitingTimeOptimistic'] = $queueListHelper->getOptimisticWaitingTime();
        $infoData['waitingClientsBeforeNext'] = $queueListHelper->getWaitingClientsBeforeNext();
        $infoData['waitingClientsEffective'] = $queueListHelper->getWaitingClientsEffective();
        return $infoData;
    }
}
