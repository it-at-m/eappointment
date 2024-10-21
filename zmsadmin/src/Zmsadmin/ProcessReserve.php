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
 * Reserve a process
 */
class ProcessReserve extends BaseController
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
        $process = $this->getProcess($input, $scope);
        $validatedForm = static::getValidatedForm($request->getAttribute('validator'), $process);
        if ($validatedForm['failed']) {
            return \BO\Slim\Render::withJson(
                $response,
                $validatedForm
            );
        }
        
        $process = static::writeReservedProcess($input, $process);
        $process = static::writeConfirmedProcess($input, $process);
        $appointment = $process->getFirstAppointment();
        $conflictList = ($process->isWithAppointment()) ?
            ProcessSave::getConflictList($scope->getId(), $appointment) :
            null;
        $queryParams = ('confirmed' == $process->getStatus()) ?
            [
                'selectedprocess' => $process,
                'success' => 'process_reserved',
                'conflictlist' => $conflictList
            ] :
            [];

        return \BO\Slim\Render::withHtml(
            $response,
            'element/helper/messageHandler.twig',
            $queryParams
        );
    }

    protected function getProcess($input, $scope)
    {
        $process = new \BO\Zmsentities\Process();
        $selectedTime = str_replace('-', ':', $input['selectedtime']);
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $input['selecteddate'] .' '. $selectedTime);
        
        return $process->withUpdatedData($input, $dateTime, $scope);
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
            ->validateText(
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

        if (isset($process->scope->preferences['client']['customTextfieldRequired'])
            && $process->scope->preferences['client']['customTextfieldRequired'] === true
        ) {
            $processValidator->validateCustomField(
                $validator->getParameter('customTextfield'),
                $delegatedProcess->setter('customTextfield')
            );
        }

        $processValidator->getCollection()->addValid(
            $validator->getParameter('sendConfirmation')->isNumber(),
            $validator->getParameter('sendReminder')->isNumber()
        );

        $form = $processValidator->getCollection()->getStatus(null, true);
        $form['failed'] = $processValidator->getCollection()->hasFailed();
        return $form;
    }

    public static function writeReservedProcess($input, $process)
    {
        $process = \App::$http
            ->readPostResult('/process/status/reserved/', $process, [
                'slotType' => 'intern',
                'clientkey' => \App::CLIENTKEY,
                'slotsRequired' => (isset($input['slotCount']) && 1 < $input['slotCount']) ? $input['slotCount'] : 0
            ])
            ->getEntity();
        return $process;
    }

    public static function writeConfirmedProcess($input, $process)
    {
        $confirmedProcess = \App::$http->readPostResult('/process/status/confirmed/', $process)->getEntity();
        if ('confirmed' == $confirmedProcess->getStatus()) {
            $process = $confirmedProcess;
            Helper\AppointmentFormHelper::updateMailAndNotification($input, $process);
        }
        return $process;
    }
}
