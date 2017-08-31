<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

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

    public static function writeConfirmedProcess($formData, Entity $process)
    {
        $process = \App::$http->readPostResult('/process/status/confirmed/', $process)->getEntity();
        if ('confirmed' == $process->status) {
            static::updateMailAndNotificationCount($formData, $process);
        }
        return $process;
    }

    public static function writeQueuedProcess($formData, Entity $process)
    {
        $process = \App::$http->readPostResult('/workstation/process/waitingnumber/', $process)->getEntity();
        if ('queued' == $process->status) {
            static::updateMailAndNotificationCount($formData, $process);
        }
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
        //$client = $process->getFirstClient();
        if (array_key_exists('sendMailConfirmation', $formData) &&
            1 == $formData['sendMailConfirmation']['value'] /*&&
            $client->getEmailSendCount() == 0
            */ #31526 - allow new confirmation mail on counter or workstation changes
        ) {
            \App::$http->readPostResult(
                '/process/'. $process->id .'/'. $process->authKey .'/confirmation/mail/',
                $process
            );
        }
        if (array_key_exists('sendConfirmation', $formData) &&
            1 == $formData['sendConfirmation']['value'] /*&&
            $client->getNotificationsSendCount() == 0
            */ #31526 - allow new confirmation mail on counter or workstation changes
        ) {
            \App::$http->readPostResult(
                '/process/'. $process->id .'/'. $process->authKey .'/confirmation/notification/',
                $process
            );
        }
    }
}
