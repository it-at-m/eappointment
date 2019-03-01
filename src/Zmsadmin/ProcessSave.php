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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $scope = Helper\AppointmentFormHelper::readSelectedScope($request, $workstation);
        $input = $request->getParams();
        $validatedForm = FormValidation::fromAdminParameters($scope['preferences'], true);
        if ($validatedForm->hasFailed()) {
            return \BO\Slim\Render::withJson(
                $response,
                $validatedForm->getStatus(null, true)
            );
        }
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/')->getEntity();
        $process = $this->writeSavedProcess($scope, $process, $input);
        $success = ($process->toProperty()->queue->withAppointment->get()) ?
          'process_updated' :
          'process_withoutappointment_updated';

        return \BO\Slim\Render::withHtml(
            $response,
            'element/helper/messageHandler.twig',
            array(
                'selectedprocess' => $process,
                'success' => $success
            )
        );
    }

    protected function writeSavedProcess($scope, $process, $input)
    {
        $initiator = Validator::param('initiator')->isString()->getValue();
        if ($process->toProperty()->queue->withAppointment->get()) {
            $dateTime = (new \DateTime())->setTimestamp($process->getFirstAppointment()->date);
            $process->withUpdatedData($input, $dateTime, $scope);
            $process = Helper\AppointmentFormHelper::writeUpdatedProcess($input, $process, $initiator);
        } else {
            $process = Helper\AppointmentFormHelper::writeUpdateQueuedProcess($input, $process, $initiator);
        }
        return $process;
    }
}
