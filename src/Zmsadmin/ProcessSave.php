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
 * Update a process
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
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/')->getEntity();
        $input = $request->getParsedBody();
        $process = $this->writeSavedProcess($request, $process, $input);
        $success = ($process->toProperty()->queue->withAppointment->get()) ?
          'process_updated' :
          'process_withoutappointment_updated';

        return \BO\Slim\Render::redirect(
            'appointment_form',
            array(),
            array(
                'selectedprocess' => $process->getId(),
                'success' => $success
            )
        );
    }

    protected function writeSavedProcess($request, $process, $input)
    {
        $initiator = Validator::param('initiator')->isString()->getValue();
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        if ($process->toProperty()->queue->withAppointment->get()) {
            $dateTime = (new \DateTime())->setTimestamp($process->getFirstAppointment()->date);
            $scope = Helper\AppointmentFormHelper::readPreferedScope($request, $input['scope'], $workstation);
            $process->withUpdatedData($input, $dateTime, $scope);
            $process = Helper\AppointmentFormHelper::writeUpdatedProcess($input, $process, $initiator);
        } else {
            $process = Helper\AppointmentFormHelper::writeUpdateQueuedProcess($input, $process, $initiator);
        }
        return $process;
    }
}
