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
    public static function getInfoBoxData(\BO\Zmsentities\Workstation $workstation, $dateString)
    {
        $infoData = array();
        $scope = new \BO\Zmsentities\Scope($workstation->scope);
        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = \App::$http->readGetResult('/scope/'. $scope->id .'/cluster/')->getEntity();
            $cluster = \App::$http->readGetResult('/cluster/'. $cluster->id . '/workstationcount/')->getEntity();
            $queueList = \App::$http->readGetResult('/cluster/'. $cluster->id . '/queue/')->getCollection();
            $infoData['workstationCount'] = $cluster->getScopesWorkstationCount();
        } else {
            $dateTime = new \BO\Zmsentities\Helper\DateTime($dateString);
            $scope = \App::$http->readGetResult('/scope/'. $scope->id . '/workstationcount/')->getEntity();
            $queueList =  \App::$http->readGetResult('/scope/'. $scope->id . '/queue/')->getCollection();
            $availabilityList = \App::$http
                ->readGetResult('/scope/'. $scope->id . '/availability/')
                ->getCollection()
                ->withDateTime($dateTime);
            $infoData['workstationCount'] = $scope->status['queue']['workstationCount'];
            $infoData['workstationGhostCount'] = $scope->status['queue']['ghostWorkstationCount'];
            $infoData['availabilities'] = $availabilityList->getArrayCopy();
        }
        $infoData['waitingTime'] = $queueList->getLast()->waitingTimeEstimate;
        $infoData['queueCount'] = $queueList->count();
        return $infoData;
    }
}
