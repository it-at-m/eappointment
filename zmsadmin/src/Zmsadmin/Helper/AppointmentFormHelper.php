<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Zmsentities\Collection\ScopeList;

/**
 * @SuppressWarnings(Complexity)
 */
class AppointmentFormHelper
{
    public static function readFreeProcessList($request, $workstation, $resolveReferences = 1)
    {
        $validator = $request->getAttribute('validator');
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $selectedProcess = ($selectedProcessId)
            ? \App::$http->readGetResult('/process/'. $selectedProcessId .'/', [
                'gql' => GraphDefaults::getProcess()
            ])->getEntity()
            : null;

        $scope = static::readSelectedScope($request, $workstation, $selectedProcess, $resolveReferences);
        $scopeList = ($scope) ? (new ScopeList)->addEntity($scope) : (new ClusterHelper($workstation))->getScopeList();
        
        $slotType = static::setSlotType($validator);
        $slotsRequired = static::setSlotsRequired($validator, $scope, $selectedProcess);
        $freeProcessList = static::readProcessListByScopeAndDate(
            $validator,
            $scopeList,
            $slotType,
            $slotsRequired
        );
        $freeProcessList = ($freeProcessList) ? $freeProcessList->withoutExpiredAppointmentDate(\App::$now) : null;
        $freeProcessList = static::getFreeProcessListWithSelectedProcess(
            $validator,
            $scopeList,
            $freeProcessList,
            $selectedProcess
        );
        return ($freeProcessList) ? $freeProcessList->toProcessListByTime()->sortByTimeKey() : null;
    }

    public static function readRequestList($request, $workstation, $selectedScope = null)
    {
        $scope = ($selectedScope) ? $selectedScope : static::readSelectedScope($request, $workstation);
        $requestList = null;
        if ($scope) {
            $requestList = \App::$http
                ->readGetResult('/scope/'. $scope->getId().'/request/', [
                    'gql' => GraphDefaults::getRequest()
                ])
                ->getCollection();
        }
        return ($requestList) ? $requestList->sortByName() : new \BO\Zmsentities\Collection\RequestList;
    }

    public static function readSelectedScope($request, $workstation, $selectedProcess = null, $resolveReferences = 1)
    {
        $validator = $request->getAttribute('validator');
        $input = $request->getParsedBody();
        $selectedScopeId = (isset($input['scope']))
            ? $input['scope']
            : $validator->getParameter('selectedscope')->isNumber()->getValue();

        if ($workstation->queue['clusterEnabled'] && ! $selectedScopeId) {
            $selectedScope = null;
        }
        if (! $workstation->queue['clusterEnabled'] && ! $selectedScopeId) {
            $selectedScope = new \BO\Zmsentities\Scope($workstation->scope);
        }
        if ($selectedScopeId) {
            $selectedScope = \App::$http
              ->readGetResult('/scope/'. $selectedScopeId .'/', [
                  'resolveReferences' => $resolveReferences,
                  'gql' => GraphDefaults::getScope()
                ])
              ->getEntity();
        }
        if (! $workstation->queue['clusterEnabled'] && $selectedProcess && $selectedProcess->hasId()) {
            $selectedScope = $selectedProcess->getCurrentScope();
        }
        return $selectedScope;
    }

    public static function readSelectedProcess($request)
    {
        $validator = $request->getAttribute('validator');
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        return ($selectedProcessId) ?
            \App::$http->readGetResult('/process/'. $selectedProcessId .'/', [
                'gql' => GraphDefaults::getProcess()
            ])->getEntity() :
            null;
    }

    public static function updateMailAndNotification($formData, \BO\Zmsentities\Process $process)
    {
        if (isset($formData['sendMailConfirmation'])) {
            $mailConfirmation = $formData['sendMailConfirmation'];
            $mailConfirmation = (isset($mailConfirmation['value'])) ? $mailConfirmation['value'] : $mailConfirmation;
            self::writeMail($mailConfirmation, $process);
        }
        if (isset($formData['sendConfirmation'])) {
            $smsConfirmation = $formData['sendConfirmation'];
            $smsConfirmation = (isset($smsConfirmation['value'])) ? $smsConfirmation['value'] : $smsConfirmation;
            self::writeNotification($smsConfirmation, $process);
        }
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
        if ($selectedProcess &&
            $selectedProcess->queue->withAppointment &&
            $selectedDate == $selectedProcess->getFirstAppointment()->toDateTime()->format('Y-m-d')
        ) {
            if ($freeProcessList) {
                $freeProcessList->setTempAppointmentToProcess(
                    $selectedProcess->getFirstAppointment()->toDateTime(),
                    $scopeList->getFirst()->getId()
                );
            } elseif (! $freeProcessList) {
                $freeProcessList = (new \BO\Zmsentities\Collection\ProcessList())->addEntity($selectedProcess);
            }
        }
        
        return ($freeProcessList) ? $freeProcessList : null;
    }

    protected static function setSlotType($validator)
    {
        $slotType = $validator->getParameter('slotType')->isString()->getValue();
        $slotType = ($slotType) ? $slotType : 'intern';
        return $slotType;
    }

    protected static function setSlotsRequired($validator, \BO\Zmsentities\Scope $scope, $process)
    {
        $slotsRequired = 0;
        if ($scope && $scope->getPreference('appointment', 'multipleSlotsEnabled')) {
            $slotsRequired = $validator->getParameter('slotsRequired')->isNumber()->getValue();
            $slotsRequired = ($slotsRequired) ? $slotsRequired : 0;
        }
        $slotsRequired = (0 == $slotsRequired && $process)
            ? $process->getFirstAppointment()->getSlotCount()
            : $slotsRequired;
        return $slotsRequired;
    }

    protected static function writeNotification($smsConfirmation, \BO\Zmsentities\Process $process)
    {
        if ($smsConfirmation &&
            $process->scope->hasNotificationEnabled() &&
            $process->getFirstClient()->hasTelephone()
        ) {
            \App::$http->readPostResult(
                '/process/'. $process->id .'/'. $process->authKey .'/confirmation/notification/',
                $process
            );
        }
    }

    protected static function writeMail($mailConfirmation, \BO\Zmsentities\Process $process)
    {
        if ($mailConfirmation &&
            $process->getFirstClient()->hasEmail() &&
            $process->scope->hasEmailFrom()
        ) {
            \App::$http->readPostResult(
                '/process/'. $process->id .'/'. $process->authKey .'/confirmation/mail/',
                $process
            );
        }
    }
}
