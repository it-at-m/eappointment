<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Mellon\Condition;
use BO\Slim\Render;
use BO\Zmsentities\Validator\ProcessValidator;
use BO\Zmsentities\Process as Entity;
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

        $validator = $request->getAttribute('validator');
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        
        if ($selectedProcessId) {
            $process = $this->readSelectedProcessWithWaitingnumber($selectedProcessId);
            if ($process && $validator->getParameter('print')->isNumber()->getValue()) {
                return \BO\Slim\Render::withHtml(
                    $response,
                    'page/printWaitingNumber.twig',
                    array(
                        'title' => ($process->isWithAppointment()) ? 'Vorgangsnummer drucken' : 'Wartenummer drucken',
                        'process' => $process
                    )
                );
            }
        }

        $input = $request->getParams();
        $scope = AppointmentFormHelper::readSelectedScope($request, $workstation);
        $process = $this->getProcess($input, $scope);
        $validatedForm = static::getValidatedForm($validator, $process);
        if ($validatedForm['failed']) {
            return \BO\Slim\Render::withJson(
                $response,
                $validatedForm
            );
        }

        $process = $this->writeQueuedProcess($input, $process);
        return \BO\Slim\Render::withHtml(
            $response,
            'element/helper/messageHandler.twig',
            array(
                'selectedprocess' => $process,
                'success' => 'process_queued'
            )
        );
    }

    public static function getValidatedForm($validator, $process)
    {
        $processValidator = new ProcessValidator($process);
        $delegatedProcess = $processValidator->getDelegatedProcess();
        $processValidator
            ->validateName(
                $validator->getParameter('familyName'),
                $delegatedProcess->setter('clients', 0, 'familyName')
            )
            ->validateMail(
                $validator->getParameter('email'),
                $delegatedProcess->setter('clients', 0, 'email')
            )
            ->validateTelephone(
                $validator->getParameter('telephone'),
                $delegatedProcess->setter('clients', 0, 'telephone')
            )
            ->validateSurvey(
                $validator->getParameter('surveyAccepted'),
                $delegatedProcess->setter('clients', 0, 'surveyAccepted')
            )
            ->validateAmendment(
                $validator->getParameter('amendment'),
                $delegatedProcess->setter('amendment')
            )
            /*
            ->validateCustomTextfield(
                $validator->getParameter('customTextfield'),
                $delegatedProcess->setter('customTextfield')
            )*/ 
            ->validateReminderTimestamp(
                $validator->getParameter('headsUpTime'),
                $delegatedProcess->setter('reminderTimestamp'),
                new Condition(
                    $validator->getParameter('sendReminder')->isNumber()->isNotEqualTo(1)
                )
            )
            
        ;
        $processValidator->getCollection()->addValid(
            $validator->getParameter('sendConfirmation')->isNumber(),
            $validator->getParameter('sendReminder')->isNumber()
        );

        $form = $processValidator->getCollection()->getStatus(null, true);
        $form['failed'] = $processValidator->getCollection()->hasFailed();
        return $form;
    }

    protected function readSelectedProcessWithWaitingnumber($selectedProcessId)
    {
        $result = null;
        if ($selectedProcessId) {
            $result = \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity();
        }
        return $result;
    }

    protected function getProcess($input, $scope)
    {
        $process = new \BO\Zmsentities\Process();
        $notice = (! $this->isOpened($scope)) ? 'Außerhalb der Öffnungszeiten gebucht! ' : '';
        return $process->withUpdatedData($input, \App::$now, $scope, $notice);
    }

    protected function writeQueuedProcess($input, $process)
    {
        $process = \App::$http->readPostResult('/workstation/process/waitingnumber/', $process)->getEntity();
        AppointmentFormHelper::updateMailAndNotification($input, $process);
        return $process;
    }

    protected function isOpened($scope)
    {
        if ($scope->getResolveLevel() < 1) {
            $scope =  \App::$http->readGetResult('/scope/'. $scope->getId() .'/', [
                'resolveReferences' => 1,
                'gql' => Helper\GraphDefaults::getScope()
            ])
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
        return $isOpened;
    }
}
