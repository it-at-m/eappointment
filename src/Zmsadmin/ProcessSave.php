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
class ProcessSave extends BaseController
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
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $process = \App::$http->readGetResult('/workstation/process/'. $processId .'/get/')->getEntity();
        $input = $request->getParsedBody();

        $validationList = FormValidation::fromAdminParameters($workstation->scope['preferences']);
        if ($validationList->hasFailed()) {
            return \BO\Slim\Render::withJson(
                $response,
                $validationList->getStatus(),
                428
            );
        }
        $dateTime = (new \DateTimeImmutable())->setTimestamp($process->getFirstAppointment()->date);
        $process->withUpdatedData($validationList->getStatus(), $input, null, $dateTime);
        $processUpdated = \App::$http
            ->readPostResult('/process/'. $process->id .'/'. $process->authKey .'/', $process)->getEntity();
        if ($processUpdated) {
            $this->writeNotification($process, $validationList->getStatus());
            return \BO\Slim\Render::withHtml(
                $response,
                'block/process/updated.twig',
                array(
                    'process' => $processUpdated
                )
            );
        }
        throw \Exception("Updating process with ID $process->id failed");
    }

    private function writeNotification(\BO\Zmsentities\Process $process, $formData)
    {
        $client = $process->getFirstClient();
        if (array_key_exists('sendMailConfirmation', $formData) &&
            1 == $formData['sendMailConfirmation']['value'] &&
            $client->getEmailSendCount() == 0
        ) {
            \App::$http->readPostResult(
                '/process/'. $process->id .'/'. $process->authKey .'/confirmation/mail/',
                $process
            );
        }
        if (array_key_exists('sendConfirmation', $formData) &&
            1 == $formData['sendConfirmation']['value'] &&
            $client->getNotificationsSendCount() == 0) {
            \App::$http->readPostResult(
                '/process/'. $process->id .'/'. $process->authKey .'/confirmation/notification/',
                $process
            );
        }
    }
}
