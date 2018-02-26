<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Zmsentities\Process as Entity;

use \BO\Zmsentities\Collection\ScopeList;

use BO\Zmsentities\Helper\ProcessFormValidation as FormValidation;

/**
 * @SuppressWarnings(Complexity)
 *
 */
class AppointmentFormHelper
{
    public static function writeUpdatedProcess($input, Entity $process, $initiator)
    {
        $process = \App::$http->readPostResult(
            '/process/'. $process->id .'/'. $process->authKey .'/',
            $process,
            ['initiator' => $initiator]
        )->getEntity();
        static::updateMailAndNotification($input, $process);
        return $process;
    }

    public static function writeUpdateQueuedProcess($input, Entity $process, $initiator)
    {
        $process->updateRequests('dldb', isset($input['requests']) ? implode(',', $input['requests']) : 0);
        $process->addAmendment($input);
        $process->addClientFromForm($input);
        $process = \App::$http->readPostResult(
            '/process/'. $process->id .'/'. $process->authKey .'/',
            $process,
            ['initiator' => $initiator]
        )->getEntity();
        static::updateMailAndNotification($input, $process);
        return $process;
    }

    public static function writeQueuedProcess($request, $workstation, \DateTimeImmutable $dateTime)
    {
        $input = $request->getParsedBody();
        $scopeId = (isset($input['scope'])) ? $input['scope'] : 0;
        $scope = static::readPreferedScope($request, $scopeId, $workstation);
        $isOpened = \App::$http
            ->readGetResult('/scope/'. $scope->id .'/availability/', ['resolveReferences' => 2])
            ->getCollection()
            ->isOpened(\App::$now);
        $notice = (! $isOpened) ? 'Außerhalb der Öffnungszeiten gebucht! ' : '';
        $process = (new Entity)->createFromScope($scope, $dateTime);
        $process->updateRequests('dldb', isset($input['requests']) ? implode(',', $input['requests']) : 0);
        $process->addClientFromForm($input);
        $process->addReminderTimestamp($input, $dateTime);
        $process->addAmendment($input, $notice);
        $process = \App::$http->readPostResult('/workstation/process/waitingnumber/', $process)->getEntity();
        static::updateMailAndNotification($input, $process);
        return $process;
    }

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
            $freeProcessList = $calendar->readAvailableSlotsFromDayAndScopeList($scopeList, $slotType, $slotsRequired);
            if (0 < $freeProcessList->getAppointmentList()->count()) {
                $scope = $freeProcessList->getAppointmentList()->getFirst()->getScope();
            }
        }
        return $scope;
    }

    public static function updateMailAndNotification($formData, Entity $process)
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

    protected static function writeNotification($smsConfirmation, Entity $process)
    {
        if ($smsConfirmation &&
            $process->scope->getPreference('appointment', 'notificationConfirmationEnabled') &&
            $process->getFirstClient()->hasTelephone()
        ) {
            \App::$http->readPostResult(
                '/process/'. $process->id .'/'. $process->authKey .'/confirmation/notification/',
                $process
            );
        }
    }

    protected static function writeMail($mailConfirmation, Entity $process)
    {
        if ($mailConfirmation && $process->getFirstClient()->hasEmail()) {
            \App::$http->readPostResult(
                '/process/'. $process->id .'/'. $process->authKey .'/confirmation/mail/',
                $process
            );
        }
    }

    protected static function getValidatedForm($request, $workstation)
    {
        $input = $request->getParsedBody();
        $scope = static::readPreferedScope($request, $input['scope'], $workstation);
        $validationList = FormValidation::fromAdminParameters($scope['preferences']);
        return $validationList;
    }

    public static function readSelectedProcess($request)
    {
        $validator = $request->getAttribute('validator');
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        return ($selectedProcessId) ?
            \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity() :
            null;
    }

    public static function handlePostRequests($request, $workstation, $selectedProcess)
    {
        $input = $request->getParsedBody();
        $validatedForm = static::getValidatedForm($request, $workstation);
        if ($validatedForm->hasFailed() && ! isset($input['delete']) && ! isset($input['queue'])) {
            return $validatedForm;
        }
        if (isset($input['reserve'])) {
            return \BO\Slim\Render::redirect(
                'processReserve',
                array(),
                array(),
                307
            );
        }
        if (isset($input['update'])) {
            return \BO\Slim\Render::redirect(
                'processSave',
                array(
                  'id' => $selectedProcess->getId()
                ),
                array(),
                307
            );
        }
        if (isset($input['queue'])) {
            return \BO\Slim\Render::redirect(
                'processQueue',
                array(),
                array(),
                307
            );
        }
        if (isset($input['delete'])) {
            return \BO\Slim\Render::redirect(
                'processDelete',
                array('id' => $input['processId']),
                array()
            );
        }
    }
}
