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
use BO\Zmsentities\Helper\ProcessFormValidation as FormValidation;

/**
 * Delete a process
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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();

        $selectedDate = Validator::value($args['date'])->isString()->getValue();
        $selectedTime = Validator::value($args['time'])->isString()->getValue();
        $selectedTime = $selectedTime ? str_replace('-', ':', $selectedTime) : '00:00:00';
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $selectedDate .' '. $selectedTime);
        $input = $request->getParsedBody();
        $process = new \BO\Zmsentities\Process();

        if ($selectedDate && $selectedTime && is_array($input)) {
            $validationList = FormValidation::fromAdminParameters($workstation->scope['preferences']);
            if ($validationList->hasFailed()) {
                return \BO\Slim\Render::withJson(
                    $response,
                    $validationList->getStatus(),
                    428
                );
            }

            $process->createFromFormData($dateTime, $workstation->scope, $validationList->getStatus(), $input);
            $reservedProcess = \App::$http->readPostResult('/process/status/reserved/', $process)->getEntity();

            if ($reservedProcess) {
                $process = \App::$http->readGetResult(
                    '/process/'. $reservedProcess->id .'/'. $reservedProcess->authKey .'/'
                )->getEntity();
                $process = $this->writeProcessConfirmation($process, $validationList->getStatus());
            }
        }
        return \BO\Slim\Render::withJson(
            $response,
            [
                'id' => $process->id,
                'status' => $process->status
            ]
        );
    }

    private function writeProcessConfirmation(\BO\Zmsentities\Process $process, $formData)
    {
        $process = \App::$http->readPostResult('/process/status/confirmed/', $process)->getEntity();
        if (array_key_exists('sendMailConfirmation', $formData) && 1 == $formData['sendMailConfirmation']['value']) {
            \App::$http->readPostResult(
                '/process/'. $process->id .'/'. $process->authKey .'/confirmation/mail/',
                $process
            );
        }
        if (array_key_exists('sendConfirmation', $formData) && 1 == $formData['sendConfirmation']['value']) {
            \App::$http->readPostResult(
                '/process/'. $process->id .'/'. $process->authKey .'/confirmation/notification/',
                $process
            );
        }
        return $process;
    }
}
