<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin\Helper;

use BO\Zmsentities\Availability;
use BO\Zmsentities\Collection\ScopeList;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Provider;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Process;
use BO\Zmsentities\Workstation;

/**
 * @SuppressWarnings(Complexity)
 */
class AppointmentFormHelper
{
    public static function readFreeProcessList(\Psr\Http\Message\RequestInterface $request, \BO\Zmsentities\Workstation $workstation, int $resolveReferences = 1)
    {
        $validator = $request->getAttribute('validator');
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $selectedProcess = ($selectedProcessId)
            ? \App::$http->readGetResult('/process/' . $selectedProcessId . '/', [
                'gql' => GraphDefaults::getProcess()
            ])->getEntity()
            : null;

        $scope = static::readSelectedScope($request, $workstation, $selectedProcess, $resolveReferences);
        $scopeList = ($scope) ? (new ScopeList())->addEntity($scope) : (new ClusterHelper($workstation))->getScopeList();

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

    public static function readRequestList(\Psr\Http\Message\RequestInterface $request, \BO\Zmsentities\Workstation $workstation, ?Scope $selectedScope = null)
    {
        $scope = ($selectedScope) ? $selectedScope : static::readSelectedScope($request, $workstation);
        $requestList = null;
        if ($scope) {
            $requestList = \App::$http
                ->readGetResult('/scope/' . $scope->getId() . '/request/', [
                    'gql' => GraphDefaults::getRequest()
                ])
                ->getCollection();
        }
        return ($requestList) ? $requestList->sortByName() : new RequestList();
    }

    public static function readSelectedScope(\Psr\Http\Message\RequestInterface $request, \BO\Zmsentities\Workstation $workstation, ?Process $selectedProcess = null, int $resolveReferences = 1)
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
            $selectedScope = new Scope($workstation->scope);
        }
        if ($selectedScopeId) {
            $selectedScope = \App::$http
              ->readGetResult('/scope/' . $selectedScopeId . '/', [
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

    public static function readSelectedProcess(\Psr\Http\Message\RequestInterface $request)
    {
        $validator = $request->getAttribute('validator');
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        return ($selectedProcessId) ?
            \App::$http->readGetResult('/process/' . $selectedProcessId . '/', [
                'gql' => GraphDefaults::getProcess()
            ])->getEntity() :
            null;
    }

    public static function updateMail(array $formData, Process $process): void
    {
        if (isset($formData['sendMailConfirmation'])) {
            $mailConfirmation = $formData['sendMailConfirmation'];
            $mailConfirmation = (isset($mailConfirmation['value'])) ? $mailConfirmation['value'] : $mailConfirmation;
            self::writeMail((bool) $mailConfirmation, $process);
        }
    }

    protected static function readProcessListByScopeAndDate(\BO\Mellon\Validator $validator, \BO\Zmsentities\Collection\ScopeList $scopeList, string $slotType, int $slotsRequired)
    {
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $calendar = new Calendar($selectedDate);
        return $calendar->readAvailableSlotsFromDayAndScopeList($scopeList, $slotType, $slotsRequired);
    }

    protected static function getFreeProcessListWithSelectedProcess(
        \BO\Mellon\Validator $validator,
        \BO\Zmsentities\Collection\ScopeList $scopeList,
        ?\BO\Zmsentities\Collection\ProcessList $freeProcessList,
        ?\BO\Zmsentities\Process $selectedProcess
    ) {
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        if (
            $selectedProcess &&
            $selectedProcess->queue->withAppointment &&
            $selectedDate == $selectedProcess->getFirstAppointment()->toDateTime()->format('Y-m-d')
        ) {
            if ($freeProcessList) {
                $freeProcessList->setTempAppointmentToProcess(
                    $selectedProcess->getFirstAppointment()->toDateTime(),
                    $scopeList->getFirst()->getId()
                );
            } elseif (! $freeProcessList) {
                $freeProcessList = (new ProcessList())->addEntity($selectedProcess);
            }
        }

        return ($freeProcessList) ? $freeProcessList : null;
    }

    protected static function setSlotType(\BO\Mellon\Validator $validator)
    {
        $slotType = $validator->getParameter('slotType')->isString()->getValue();
        $slotType = ($slotType) ? $slotType : 'intern';
        return $slotType;
    }

    protected static function setSlotsRequired(\BO\Mellon\Validator $validator, ?Scope $scope, ?\BO\Zmsentities\Process $process)
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

    protected static function writeMail(bool $mailConfirmation, Process $process): void
    {
        if (
            $mailConfirmation &&
            $process->getFirstClient()->hasEmail() &&
            $process->scope->hasEmailFrom()
        ) {
            \App::$http->readPostResult(
                '/process/' . $process->id . '/' . $process->authKey . '/confirmation/mail/',
                $process
            );
        }
    }

    public static function resolveSlotTimeInMinutes(
        ?Process $selectedProcess,
        ?Scope $selectedScope,
        ?Workstation $workstation = null
    ): int|string|null {
        $availability = null;
        if ($selectedProcess && $selectedProcess->hasId()) {
            $availability = $selectedProcess->getAppointments()->getFirst()->getAvailability();
        }

        $slotTimeInMinutes = null;
        if ($availability && $availability->hasId()) {
            $slotTimeInMinutes = static::readAvailabilitySlotTimeInMinutes($availability);
        }

        if (! $slotTimeInMinutes) {
            $scopes = [$selectedScope];
            if ($selectedProcess && $selectedProcess->hasId()) {
                $scopes[] = $selectedProcess->getCurrentScope();
            }
            $scopes[] = $workstation?->getScope();

            foreach ($scopes as $scope) {
                $slotTimeInMinutes = static::readProviderSlotTimeInMinutes($scope);
                if ($slotTimeInMinutes) {
                    break;
                }
            }
        }

        return $slotTimeInMinutes;
    }

    protected static function readAvailabilitySlotTimeInMinutes(?Availability $availability): int|string|null
    {
        if (! $availability) {
            return null;
        }
        return $availability->getSlotTimeInMinutes();
    }

    protected static function readProviderSlotTimeInMinutes(?Scope $scope): int|string|null
    {
        if ($scope === null || ! isset($scope['provider'])) {
            return null;
        }
        $provider = $scope['provider'];
        if (! $provider instanceof Provider) {
            $provider = new Provider($provider);
        }
        return $provider->getSlotTimeInMinutes();
    }

    /**
     * How many stored slots one step of "Termindauer in Minuten" covers.
     * The step is the greatest common divisor of the location's service
     * lengths, counted in the stored slot size.
     */
    public static function appointmentIntervalSlots(mixed $requestList): int
    {
        $counts = [];
        if ($requestList) {
            foreach ($requestList as $request) {
                $count = (int) ($request['timeSlotCount'] ?? 0);
                if ($count > 0) {
                    $counts[] = $count;
                }
            }
        }

        return self::greatestCommonDivisorOf($counts) ?: 1;
    }

    /**
     * @return list<int>
     */
    public static function durationSlotCounts(mixed $requestList, int $steps = 20): array
    {
        $slotStep = self::appointmentIntervalSlots($requestList);
        $counts = [];
        for ($step = 1; $step <= $steps; $step++) {
            $counts[] = $step * $slotStep;
        }

        return $counts;
    }

    /**
     * @param list<int> $values
     */
    private static function greatestCommonDivisorOf(array $values): int
    {
        $current = 0;
        foreach ($values as $value) {
            $current = $current === 0 ? $value : self::greatestCommonDivisor($current, $value);
        }

        return $current;
    }

    private static function greatestCommonDivisor(int $left, int $right): int
    {
        $left = abs($left);
        $right = abs($right);
        while ($right !== 0) {
            $remainder = $left % $right;
            $left = $right;
            $right = $remainder;
        }

        return $left;
    }
}
