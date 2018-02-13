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

class AppointmentFormHelper
{
    public static function writeReservedProcess(Entity $process)
    {
        $process = \App::$http
            ->readPostResult('/process/status/reserved/', $process, ['slotType' => 'intern'])->getEntity();
        return $process;
    }

    public static function writeUpdatedProcess($formData, Entity $process, $initiator)
    {
        $process = \App::$http->readPostResult(
            '/process/'. $process->id .'/'. $process->authKey .'/',
            $process,
            ['initiator' => $initiator]
        )->getEntity();
        static::updateMailAndNotification($formData, $process);
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

    public static function writeConfirmedProcess($formData, Entity $process)
    {
        $process = \App::$http->readPostResult('/process/status/confirmed/', $process)->getEntity();
        if ('confirmed' == $process->status) {
            static::updateMailAndNotification($formData, $process);
        }
        return $process;
    }

    public static function writeQueuedProcess($request, $workstation, \DateTimeImmutable $dateTime)
    {
        $input = $request->getParsedBody();
        $scopeId = (isset($input['scope'])) ? $input['scope'] : 0;
        $scope = static::readPreferedScope($request, $scopeId, $workstation);
        $isOpened = \App::$http
            ->readGetResult('/scope/'. $scope->id .'/availability/')
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

    public static function readFreeProcessList($request, $workstation, $scope = null)
    {
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $slotType = $validator->getParameter('slottype')->isString()->getValue();
        $slotType = ($slotType) ? $slotType : 'intern';
        $slotsRequired = $validator->getParameter('slotsrequired')->isNumber()->getValue();
        $slotsRequired = ($slotsRequired) ? $slotsRequired : 0;
        $calendar = new Calendar($selectedDate);
        $scopeList = ($scope) ? (new ScopeList)->addEntity($scope) : (new ClusterHelper($workstation))->getScopeList();
        $freeProcessList = $calendar->readAvailableSlotsFromDayAndScopeList($scopeList, $slotType, $slotsRequired);
        return ($freeProcessList) ? $freeProcessList : null;
    }

    public static function readPreferedScope($request, $scopeId, $workstation)
    {
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $selectedDate = (new \DateTimeImmutable($selectedDate))->getTimestamp();
        $scope = new \BO\Zmsentities\Scope($workstation->scope);
        $clusterHelper = new ClusterHelper($workstation);
        if ($scopeId > 0) {
            $scope = \App::$http->readGetResult('/scope/'. $scopeId .'/', ['resolveReferences' => 1])->getEntity();
        } elseif ($clusterHelper->isClusterEnabled() &&
            null === static::readFreeProcessList($request, $workstation, $scope)
        ) {
            $scopeList = $clusterHelper->getScopeList();
            foreach ($scopeList as $scope) {
                $freeProcessList = static::readFreeProcessList($request, $workstation, $scope);
                if (null !== $freeProcessList && isset($freeProcessList[$selectedDate])) {
                    return $scope;
                }
            }
        }
        return $scope;
    }

    protected static function updateMailAndNotification($formData, Entity $process)
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
}
