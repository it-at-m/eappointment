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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();

        $selectedDate = Validator::value($args['date'])->isString()->getValue();
        $selectedTime = Validator::value($args['time'])->isString()->getValue();
        $selectedTime = $selectedTime ? str_replace('-', ':', $selectedTime) : '00:00:00';
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $selectedDate .' '. $selectedTime);
        $input = $request->getParsedBody();
        $process = new \BO\Zmsentities\Process();
        $scope = (new Helper\ClusterHelper($workstation))->getPreferedScopeByCluster();
        if ($selectedDate && $selectedTime && is_array($input)) {
            $validationList = FormValidation::fromAdminParameters($scope['preferences']);
            if ($validationList->hasFailed()) {
                return \BO\Slim\Render::withJson(
                    $response,
                    $validationList->getStatus(),
                    428
                );
            }
            $process->withUpdatedData($validationList->getStatus(), $input, $scope, $dateTime);
            $process = Helper\AppointmentFormHelper::writeReservedProcess($process);
            $process = Helper\AppointmentFormHelper::writeConfirmedProcess($validationList->getStatus(), $process);
        }
        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/reserved.twig',
            array(
                'process' => $process
            )
        );
    }
}
