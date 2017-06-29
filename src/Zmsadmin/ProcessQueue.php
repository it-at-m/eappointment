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
 * Queue a process from appointment formular without appointment
 */
class ProcessQueue extends BaseController
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
        $validator = $request->getAttribute('validator');
        $selectedDate = Validator::value($args['date'])->isString()->getValue();
        $dateTime = ($selectedDate) ? \DateTime::createFromFormat('Y-m-d H:i', $selectedDate .' 00:00') : \App::$now;

        $process = $this->readSelectedProcessWithWaitingnumber($validator);
        if ($process instanceof \BO\Zmsentities\Process) {
            return \BO\Slim\Render::withHtml(
                $response,
                'page/printWaitingNumber.twig',
                array(
                    'title' => 'Wartenummer drucken',
                    'process' => $process,
                    'currentDate' => $dateTime
                )
            );
        }

        $result = $this->readNewProcessWithoutAppointment(
            $response,
            $request->getParsedBody(),
            $workstation->scope,
            $dateTime
        );
        if ($result instanceof \BO\Zmsentities\Process) {
            return \BO\Slim\Render::withHtml(
                $response,
                'block/appointment/waitingnumber.twig',
                array(
                    'process' => $result,
                    'selectedDate' => $selectedDate
                )
            );
        }
        return $result;
    }

    protected function readSelectedProcessWithWaitingnumber($validator)
    {
        $result = null;
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $isPrint = $validator->getParameter('print')->isNumber()->getValue();
        if ($selectedProcessId && $isPrint) {
            $result = \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity();
        }
        return $result;
    }

    protected function readNewProcessWithoutAppointment($response, $input, $scope, $dateTime)
    {
        $result = null;
        $scope = new \BO\Zmsentities\Scope($scope);
        $process = (new \BO\Zmsentities\Process)->createFromScope($scope, $dateTime);
        if (is_array($input)) {
            $validationList = FormValidation::fromAdminParameters($scope['preferences']);
            if ($validationList->hasFailed()) {
                $result = \BO\Slim\Render::withJson(
                    $response,
                    $validationList->getStatus(),
                    428
                );
            } else {
                $process->withUpdatedData($validationList->getStatus(), $input, $scope, $dateTime);
                $result = Helper\AppointmentFormHelper::writeQueuedProcess($validationList->getStatus(), $process);
            }
        }
        return $result;
    }
}
