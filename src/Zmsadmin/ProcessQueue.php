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
 * Queue a process
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
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $selectedDate .' 00:00');
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $isPrint = $validator->getParameter('print')->isNumber()->getValue();

        if ($selectedProcessId && $isPrint) {
            $selectedProcess = \App::$http
                ->readGetResult('/process/'. $selectedProcessId .'/')->getEntity();
            return \BO\Slim\Render::withHtml(
                $response,
                'page/printWaitingNumber.twig',
                array(
                    'title' => 'Wartenummer drucken',
                    'process' => $selectedProcess
                )
            );
        }

        $input = $request->getParsedBody();
        $scope = new \BO\Zmsentities\Scope($workstation->scope);
        $process = (new \BO\Zmsentities\Process)->createFromScope($scope, $dateTime);
        if (is_array($input)) {
            $validationList = FormValidation::fromAdminParameters($scope['preferences']);
            if ($validationList->hasFailed()) {
                return \BO\Slim\Render::withJson(
                    $response,
                    $validationList->getStatus(),
                    428
                );
            }
            $process->withUpdatedData($validationList->getStatus(), $input, $scope, $dateTime);
            $process = Helper\AppointmentFormHelper::writeQueuedProcess($validationList->getStatus(), $process);
        }
        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/waitingnumber.twig',
            array(
                'process' => $process,
                'selectedDate' => $selectedDate
            )
        );
    }
}
