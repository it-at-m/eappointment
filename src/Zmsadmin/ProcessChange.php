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

/**
 * Change process data but keep id
 */
class ProcessChange extends BaseController
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
        $input = $request->getParams();
        $scope = Helper\AppointmentFormHelper::readSelectedScope($request, $workstation);
        $oldProcess = \App::$http->readGetResult('/process/'. $input['selectedprocess'] .'/')->getEntity();
        $newProcess = $this->getNewProcess($input, $oldProcess, $scope);
        $validatedForm = static::getValidatedForm($request->getAttribute('validator'), $newProcess);
        if ($validatedForm['failed']) {
            return \BO\Slim\Render::withJson(
                $response,
                $validatedForm
            );
        }
        
        $process = static::writeChangedProcess($oldProcess, $newProcess);
        $queryParams = ('confirmed' == $process->getStatus()) ?
            ['selectedprocess' => $process, 'success' => 'process_changed'] :
            [];

        return \BO\Slim\Render::withHtml(
            $response,
            'element/helper/messageHandler.twig',
            $queryParams
        );
    }

    protected function getNewProcess($input, $process, $scope)
    {
        $newProcess = clone $process;
        $selectedTime = str_replace('-', ':', $input['selectedtime']);
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $input['selecteddate'] .' '. $selectedTime);
        return $newProcess->withUpdatedData($input, $dateTime, $scope);
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
            ->validateRequests(
                $validator->getParameter('requests'),
                function () use ($process, $delegatedProcess) {
                    $arrayKeys = array_keys(json_decode(json_encode($process->requests), true));
                    foreach ($arrayKeys as $key) {
                        $delegatedProcess->setter('requests', $key, 'id');
                        $delegatedProcess->setter('requests', $key, 'source');
                    }
                }
            )
            ->validateMail(
                $validator->getParameter('email'),
                $delegatedProcess->setter('clients', 0, 'email'),
                new Condition(
                    $validator->getParameter('sendMailConfirmation')->isNumber()->isNotEqualTo(1),
                    $validator->getParameter('surveyAccepted')->isString()->isDevoidOf([1])
                )
            )
            ->validateTelephone(
                $validator->getParameter('telephone'),
                $delegatedProcess->setter('clients', 0, 'telephone'),
                new Condition(
                    $validator->getParameter('sendConfirmation')->isNumber()->isNotEqualTo(1),
                    $validator->getParameter('sendReminder')->isNumber()->isNotEqualTo(1)
                )
            )
            ->validateSurvey(
                $validator->getParameter('surveyAccepted'),
                $delegatedProcess->setter('clients', 0, 'surveyAccepted')
            )
            ->validateAmendment(
                $validator->getParameter('amendment'),
                $delegatedProcess->setter('amendment')
            )
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

    public static function writeChangedProcess($oldProcess, $newProcess)
    {
        $oldAppointment = $oldProcess->getFirstAppointment();
        $newAppointment = $newProcess->getFirstAppointment();

        error_log('matching Appointment: ' . $oldAppointment->isMatching($newAppointment));
        error_log('old appointment: ' . $oldAppointment);
        error_log('new appointment: ' . $newAppointment);
        if (! $oldAppointment->isMatching($newAppointment)) {
            $newProcess = \App::$http->readPostResult(
                '/process/'. $oldProcess->id .'/'. $oldProcess->authKey .'/appointment/',
                $newAppointment,
                [
                    'slotType' => 'intern',
                    'clientkey' => \App::CLIENTKEY,
                    'slotsRequired' => (isset($input['slotCount']) && 1 < $input['slotCount']) ? $input['slotCount'] : 0
                ]
            )->getEntity();
        }
        $newProcess = static::writeConfirmedMailAndNotification($input, $newProcess);
        return $newProcess;
    }

    public static function writeConfirmedMailAndNotification($input, $process)
    {
        if ('confirmed' == $process->getStatus()) {
            Helper\AppointmentFormHelper::updateMailAndNotification($input, $process);
        }
        return $process;
    }
}
