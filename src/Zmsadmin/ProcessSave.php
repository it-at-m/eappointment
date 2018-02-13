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
        $result = $this->writeSavedProcess($request, $response, $process, $input);
        if ($result instanceof \Psr\Http\Message\ResponseInterface) {
            return $result;
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/updated.twig',
            array(
                'process' => $process
            )
        );
    }

    protected function writeSavedProcess($request, $response, $process, $input)
    {
        $initiator = Validator::param('initiator')->isString()->getValue();
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $dateTime = (new \DateTime())->setTimestamp($process->getFirstAppointment()->date);
        if ($process->toProperty()->queue->withAppointment->get()) {
            $scope = Helper\AppointmentFormHelper::readPreferedScope($request, $input['scope'], $workstation);
            $validationList = FormValidation::fromAdminParameters($scope['preferences']);
            if ($validationList->hasFailed()) {
                return \BO\Slim\Render::withJson(
                    $response,
                    $validationList->getStatus(),
                    428
                );
            }

            $formData = $validationList->getStatus();
            $process->withUpdatedData($formData, $input, $dateTime, $scope);
            $process = Helper\AppointmentFormHelper::writeUpdatedProcess($formData, $process, $initiator);
        } else {
            $process = Helper\AppointmentFormHelper::writeUpdateQueuedProcess($input, $process, $initiator);
        }
        return $process;
    }
}
