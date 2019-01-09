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
        $scope = static::readSelectedScope($request, $workstation);
        $scopeList = ($scope) ? (new ScopeList)->addEntity($scope) : (new ClusterHelper($workstation))->getScopeList();
        $slotType = static::setSlotType($validator);
        $slotsRequired = static::setSlotsRequired($validator, $scope);

        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $selectedProcess = ($selectedProcessId)
            ? \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity()
            : null;
        $freeProcessList = static::readProcessListByScopeAndDate($validator, $scopeList, $slotType, $slotsRequired);
        return static::getFreeProcessListWithSelectedProcess(
            $validator,
            $scopeList,
            $freeProcessList,
            $selectedProcess
        );
    }

    public static function readRequestList($request, $workstation)
    {
        $scope = static::readSelectedScope($request, $workstation);
        $requestList = new \BO\Zmsentities\Collection\RequestList;
        if ($scope) {
            $requestList = \App::$http
                ->readGetResult('/scope/'. $scope->getId().'/request/')
                ->getCollection();
        }
        return ($requestList) ? $requestList->sortByName() : $requestList;
    }

    public static function readSelectedScope($request, $workstation)
    {
        $validator = $request->getAttribute('validator');
        $input = $request->getParsedBody();
        $selectedScopeId = (isset($input['scope']))
            ? $input['scope']
            : $validator->getParameter('selectedscope')->isNumber()->getValue();
        $selectedScope = (! $workstation->queue['clusterEnabled'] && ! $selectedScopeId)
        ? new \BO\Zmsentities\Scope($workstation->scope)
        : null;

        if ($selectedScopeId) {
            $selectedScope = \App::$http
              ->readGetResult('/scope/'. $selectedScopeId .'/', ['resolveReferences' => 1])
              ->getEntity();
        }
        return $selectedScope;
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
        $slotType = $validator->getParameter('slotType')->isString()->getValue();
        $slotType = ($slotType) ? $slotType : 'intern';
        return $slotType;
    }

    protected static function setSlotsRequired($validator, $scope)
    {
        $slotsRequired = 0;
        if ($scope && $scope->getPreference('appointment', 'multipleSlotsEnabled')) {
            $slotsRequired = $validator->getParameter('slotsRequired')->isNumber()->getValue();
            $slotsRequired = ($slotsRequired) ? $slotsRequired : 0;
        }
        return $slotsRequired;
    }
}
