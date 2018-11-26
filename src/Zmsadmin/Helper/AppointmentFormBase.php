<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \BO\Zmsentities\Process as Entity;

use BO\Zmsentities\Helper\ProcessFormValidation as FormValidation;

class AppointmentFormBase
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
        $process->updateRequests(
            $process->getCurrentScope()->getSource(),
            isset($input['requests']) ? implode(',', $input['requests']) : 0
        );
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
        if ($scope->getResolveLevel() < 1) {
            $scope =  \App::$http->readGetResult('/scope/'. $scope->getId() .'/', ['resolveReferences' => 1])
                ->getEntity();
        }
        try {
            $isOpened = \App::$http
                ->readGetResult('/scope/'. $scope->id .'/availability/', ['resolveReferences' => 0])
                ->getCollection()
                ->withScope($scope)
                ->isOpened(\App::$now);
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template == 'BO\\Zmsapi\\Exception\\Availability\\AvailabilityNotFound') {
                $isOpened = false;
            }
        }
        $notice = (! $isOpened) ? 'Außerhalb der Öffnungszeiten gebucht! ' : '';
        $process = (new Entity)->createFromScope($scope, $dateTime);
        $process->updateRequests($scope->getSource(), isset($input['requests']) ? implode(',', $input['requests']) : 0);
        $process->addClientFromForm($input);
        $process->addReminderTimestamp($input, $dateTime);
        $process->addAmendment($input, $notice);
        $process = \App::$http->readPostResult('/workstation/process/waitingnumber/', $process)->getEntity();
        static::updateMailAndNotification($input, $process);
        return $process;
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
        return false;
    }

    protected static function getValidatedForm($request, $workstation)
    {
        $input = $request->getParsedBody();
        $scope = static::readPreferedScope($request, $input['scope'], $workstation);
        $validationList = FormValidation::fromAdminParameters($scope['preferences']);
        return $validationList;
    }

    protected static function writeNotification($smsConfirmation, Entity $process)
    {
        if ($smsConfirmation &&
            $process->scope->hasNotifcationEnabled() &&
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
