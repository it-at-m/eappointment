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
        $process = new \BO\Zmsentities\Process();

        $selectedTime = str_replace('-', ':', $input['selectedtime']);
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $input['selecteddate'] .' '. $selectedTime);
        $scope = Helper\AppointmentFormHelper::readPreferedScope($request, $input['scope'], $workstation);
        $process->withUpdatedData($input, $dateTime, $scope);
        $process = \App::$http
            ->readPostResult('/process/status/reserved/', $process, [
                'slotType' => 'intern',
                'slotsRequired' => (1 < $input['slotCount']) ? $input['slotCount'] : 0
            ])
            ->getEntity();
        $process = \App::$http->readPostResult('/process/status/confirmed/', $process)->getEntity();
        $queryParams = [];
        if ('confirmed' == $process->status) {
            Helper\AppointmentFormHelper::updateMailAndNotification($input, $process);
            $queryParams = array(
                'selectedprocess' => $process->getId(),
                'selectedscope' => $scope->getId(),
                'success' => 'process_reserved'
            );
        }

        return \BO\Slim\Render::redirect(
            'appointment_form',
            array(),
            $queryParams
        );
    }
}
