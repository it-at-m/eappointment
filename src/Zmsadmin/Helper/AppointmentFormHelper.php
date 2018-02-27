<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Zmsentities\Collection\ScopeList;

class AppointmentFormHelper extends AppointmentFormBase
{
    public static function readFreeProcessList($request, $workstation)
    {
        $validator = $request->getAttribute('validator');

        $selectedScopeId = $validator->getParameter('selectedscope')->isNumber()->getValue();
        $scope = static::readPreferedScope($request, $selectedScopeId, $workstation);
        $scopeList = ($scope) ? (new ScopeList)->addEntity($scope) : (new ClusterHelper($workstation))->getScopeList();
        $slotType = static::setSlotType($validator);
        $slotsRequired = static::setSlotsRequired($validator, $scope);

        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $selectedProcess = ($selectedProcessId) ?
            \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity() : null;
        $freeProcessList = static::readProcessListByScopeAndDate($validator, $scopeList, $slotType, $slotsRequired);
        return static::getFreeProcessListWithSelectedProcess(
            $validator,
            $scopeList,
            $freeProcessList,
            $selectedProcess
        );
    }

    protected static function readProcessListByScopeAndDate($validator, $scopeList, $slotType, $slotsRequired)
    {
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $calendar = new Calendar($selectedDate);
        return $calendar->readAvailableSlotsFromDayAndScopeList($scopeList, $slotType, $slotsRequired);
    }

    protected static function getFreeProcessListWithSelectedProcess(
        $validator,
        $scopeList,
        $freeProcessList,
        $selectedProcess
    ) {
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        if ($freeProcessList && $selectedProcess &&
            $selectedDate == $selectedProcess->getFirstAppointment()->toDateTime()->format('Y-m-d')
          ) {
            $dateTime = $selectedProcess->getFirstAppointment()->toDateTime();
            $freeProcessList->setTempAppointmentToProcess($dateTime, $scopeList->getFirst()->getId());
        } elseif (! $freeProcessList && $selectedProcess && $selectedProcess->queue->withAppointment) {
            $freeProcessList = (new \BO\Zmsentities\Collection\ProcessList())->addEntity($selectedProcess);
        }
        return ($freeProcessList) ? $freeProcessList->toProcessListByTime()->sortByTimeKey() : null;
    }

    protected static function setSlotType($validator)
    {
        $slotType = $validator->getParameter('slottype')->isString()->getValue();
        $slotType = ($slotType) ? $slotType : 'intern';
        return $slotType;
    }

    protected static function setSlotsRequired($validator, $scope)
    {
        $slotsRequired = 0;
        if ($scope->getPreference('appointment', 'multipleSlotsEnabled')) {
            $slotsRequired = $validator->getParameter('slotsrequired')->isNumber()->getValue();
            $slotsRequired = ($slotsRequired) ? $slotsRequired : 0;
        }
        return $slotsRequired;
    }

    public static function readPreferedScope($request, $scopeId, $workstation)
    {
        $scope = new \BO\Zmsentities\Scope($workstation->scope);
        $clusterHelper = new ClusterHelper($workstation);
        if ($scopeId > 0) {
            $scope = \App::$http->readGetResult('/scope/'. $scopeId .'/', ['resolveReferences' => 1])->getEntity();
        } elseif ($clusterHelper->isClusterEnabled()) {
            $validator = $request->getAttribute('validator');
            $slotType = $validator->getParameter('slottype')->isString()->getValue();
            $slotType = ($slotType) ? $slotType : 'intern';
            $slotsRequired = $validator->getParameter('slotsrequired')->isNumber()->getValue();
            $slotsRequired = ($slotsRequired) ? $slotsRequired : 0;
            $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
            $calendar = new Calendar($selectedDate);
            $scopeList = (new ClusterHelper($workstation))->getScopeList();
            //read free processlist of day and cluster scopelist to get scope with free processes of given day
            $freeProcessList = $calendar->readAvailableSlotsFromDayAndScopeList($scopeList, $slotType, $slotsRequired);
            if (0 < $freeProcessList->getAppointmentList()->count()) {
                $scope = $freeProcessList->getAppointmentList()->getFirst()->getScope();
            }
        }
        return $scope;
    }
}
