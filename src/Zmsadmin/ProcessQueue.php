<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsentities\Process as Entity;
use BO\Zmsentities\Helper\ProcessFormValidation as FormValidation;
use BO\Zmsadmin\Helper\AppointmentFormHelper;

/**
 * Queue a process from appointment formular without appointment
 */
class ProcessQueue extends BaseController
{

    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $scope = AppointmentFormHelper::readSelectedScope($request, $workstation);
        $validatedForm = FormValidation::fromAdminParameters($scope['preferences']);
        if ($validatedForm->hasFailed()) {
            return \BO\Slim\Render::withJson(
                $response,
                $validatedForm->getStatus(null, true)
            );
        }

        $process = $this->readSelectedProcessWithWaitingnumber($request);
        if ($process instanceof \BO\Zmsentities\Process) {
            return \BO\Slim\Render::withHtml(
                $response,
                'page/printWaitingNumber.twig',
                array(
                    'title' => 'Wartenummer drucken',
                    'process' => $process
                )
            );
        }

        $process = $this->writeQueuedProcess($request, $workstation, \App::$now);
        return \BO\Slim\Render::withHtml(
            $response,
            'element/helper/messageHandler.twig',
            array(
                'selectedprocess' => $process,
                'success' => 'process_queued'
            )
        );
    }

    protected function readSelectedProcessWithWaitingnumber($request)
    {
        $validator = $request->getAttribute('validator');
        $result = null;
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $isPrint = $validator->getParameter('print')->isNumber()->getValue();
        if ($selectedProcessId && $isPrint) {
            $result = \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity();
        }
        return $result;
    }

    protected function writeQueuedProcess($request, $workstation, \DateTimeImmutable $dateTime)
    {
        $input = $request->getParsedBody();
        $scope = AppointmentFormHelper::readSelectedScope($request, $workstation);
        if ($scope->getResolveLevel() < 1) {
            $scope =  \App::$http->readGetResult('/scope/'. $scope->getId() .'/', ['resolveReferences' => 1])
                ->getEntity();
        }
        try {
            $isOpened = \App::$http
                ->readGetResult('/scope/'. $scope->getId() .'/availability/', ['resolveReferences' => 0])
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
        AppointmentFormHelper::updateMailAndNotification($input, $process);
        return $process;
    }
}
