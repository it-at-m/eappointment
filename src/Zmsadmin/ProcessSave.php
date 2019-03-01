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
use BO\Zmsentities\Process as Entity;
use BO\Zmsentities\Helper\ProcessFormValidation as FormValidation;
use BO\Zmsadmin\Helper\AppointmentFormHelper;

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
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/')->getEntity();
        $input = $request->getParams();
        $validatedForm = FormValidation::fromAdminParameters(
            $scope['preferences'], 
            $process->isWithAppointment()
        );
        if ($validatedForm->hasFailed()) {
            return \BO\Slim\Render::withJson(
                $response,
                $validatedForm->getStatus(null, true)
            );
        }
        $process = $this->writeSavedProcess($scope, $process, $input);
        $success = ($process->isWithAppointment()) ? 'process_updated' : 'process_withoutappointment_updated';

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
        if ($process->isWithAppointment()) {
            $dateTime = (new \DateTime())->setTimestamp($process->getFirstAppointment()->date);
            $process->withUpdatedData($input, $dateTime, $scope);
            $process = $this->writeUpdatedProcess($input, $process, $initiator);
        } else {
            $process = $this->writeUpdateQueuedProcess($input, $process, $initiator);
        }
        return $process;
    }

    protected function writeUpdatedProcess($input, Entity $process, $initiator)
    {
        $process = \App::$http->readPostResult(
            '/process/'. $process->id .'/'. $process->authKey .'/',
            $process,
            ['initiator' => $initiator]
        )->getEntity();
        AppointmentFormHelper::updateMailAndNotification($input, $process);
        return $process;
    }

    protected function writeUpdateQueuedProcess($input, Entity $process, $initiator)
    {
        $process->updateRequests(
            $process->getCurrentScope()->getSource(),
            isset($input['requests']) ? implode(',', $input['requests']) : 0
        );
        $process->addAmendment($input);
        $process->addClientFromForm($input);
        $process = \App::$http->readPostResult(
            '/process/'. $process->id .'/'. $process->authKey .'/',
            $process,
            ['initiator' => $initiator]
        )->getEntity();
        AppointmentFormHelper::updateMailAndNotification($input, $process);
        return $process;
    }
}
