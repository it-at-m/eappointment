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
use BO\Zmsadmin\Helper\ProcessUpdateHelper;

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
        $process->withUpdatedData($validationList->getStatus(), $input, $workstation->scope, $dateTime);
        $process = Helper\AppointmentFormHelper::writeUpdatedProcess($validationList->getStatus(), $process);
        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/updated.twig',
            array(
                'process' => $process
            )
        );
    }
}
