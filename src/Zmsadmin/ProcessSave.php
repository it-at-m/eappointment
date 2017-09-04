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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $initiator = Validator::param('initiator')->isString()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/')->getEntity();
        $input = $request->getParsedBody();
        $scope = (new Helper\ClusterHelper($workstation))->getPreferedScopeByCluster();
        $validationList = FormValidation::fromAdminParameters($scope['preferences']);
        if ($validationList->hasFailed()) {
            return \BO\Slim\Render::withJson(
                $response,
                $validationList->getStatus(),
                428
            );
        }
        $dateTime = (new \DateTimeImmutable())->setTimestamp($process->getFirstAppointment()->date);
        $process->withUpdatedData($validationList->getStatus(), $input, $scope, $dateTime);
        $process = Helper\AppointmentFormHelper::writeUpdatedProcess(
            $validationList->getStatus(),
            $process,
            $initiator
        );
        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/updated.twig',
            array(
                'process' => $process
            )
        );
    }
}
