<?php
/**
 *
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Helper;

use \BO\Zmsentities\Process as Entity;

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
        static::updateMailAndNotificationCount($formData, $process);
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
        static::updateMailAndNotificationCount($input, $process);
        return $process;
    }

    public static function writeConfirmedProcess($formData, Entity $process)
    {
        $process = \App::$http->readPostResult('/process/status/confirmed/', $process)->getEntity();
        if ('confirmed' == $process->status) {
            static::updateMailAndNotificationCount($formData, $process);
        }
        return $process;
    }

    public static function writeQueuedProcess($input, $workstation, \DateTimeImmutable $dateTime)
    {
        $scope = (new ClusterHelper($workstation))->getPreferedScopeByCluster();
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
        static::updateMailAndNotificationCount($input, $process);
        return $process;
    }

    public static function readFreeProcessList($request, $workstation)
    {
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $slotType = $validator->getParameter('slottype')->isString()->getValue();
        $slotType = ($slotType) ? $slotType : 'intern';
        $slotsRequired = $validator->getParameter('slotsrequired')->isNumber()->getValue();
        $slotsRequired = ($slotsRequired) ? $slotsRequired : 0;
        $calendar = new Calendar($selectedDate);
        $scopeList = (new ClusterHelper($workstation))->getScopeList();
        $freeProcessList = $calendar->readAvailableSlotsFromDayAndScopeList($scopeList, $slotType, $slotsRequired);
        return ($freeProcessList) ? $freeProcessList->toProcessListByTime()->sortByTimeKey() : null;
    }

    protected static function updateMailAndNotificationCount($formData, Entity $process)
    {
        $client = $process->getFirstClient();
        if (isset($formData['sendMailConfirmation']) && $client->hasEmail()) {
            $mailConfirmation = $formData['sendMailConfirmation'];
            $mailConfirmation = (isset($mailConfirmation['value'])) ? $mailConfirmation['value'] : $mailConfirmation;
            if ($mailConfirmation) {
                \App::$http->readPostResult(
                    '/process/'. $process->id .'/'. $process->authKey .'/confirmation/mail/',
                    $process
                );
            }
        }
        if (isset($formData['sendConfirmation']) && $client->hasTelephone()) {
            $smsConfirmation = $formData['sendConfirmation'];
            $smsConfirmation = (isset($smsConfirmation['value'])) ? $smsConfirmation['value'] : $smsConfirmation;
            if ($smsConfirmation) {
                \App::$http->readPostResult(
                    '/process/'. $process->id .'/'. $process->authKey .'/confirmation/notification/',
                    $process
                );
            }
        }
    }
}
