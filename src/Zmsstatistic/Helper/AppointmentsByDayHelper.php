<?php
/**
 *
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Helper;

use \BO\Zmsentities\Collection\ProcessList;

class AppointmentsByDayHelper
{
    public static function getAppointmentsByDayForScope($workstation, $scope, $date)
    {
        $cluster = \App::$http->readGetResult('/scope/'. $scope->id .'/cluster/')->getEntity();
        $processList = new ProcessList();

        if (1 == $workstation->queue['clusterEnabled']) {
            $resultList = \App::$http
                        ->readGetResult(
                            '/cluster/'. $cluster->id .'/process/'. $date .'/',
                            ['resolveReferences' => 1]
                        )->getCollection();
        } else {
            $resultList = \App::$http
                        ->readGetResult(
                            '/scope/'. $scope->id .'/process/'. $date .'/',
                            ['resolveReferences' => 1]
                        )->getCollection();
        }
        $processList = ($resultList) ? $resultList : $processList;

        $selectedDateTime = new \DateTimeImmutable($date);
        $queueList = $processList
                   ->toQueueList($selectedDateTime)
                   ->withStatus(array('confirmed', 'queued', 'reserved'))
                   ->withSortedArrival();
        return $queueList;
    }
}
