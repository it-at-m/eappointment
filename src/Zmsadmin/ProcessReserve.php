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
        $scope = Helper\AppointmentFormHelper::readSelectedScope($request, $workstation);
        $input = $request->getParams();
        $validatedForm = FormValidation::fromAdminParameters($scope['preferences'], true);
        if ($validatedForm->hasFailed()) {
            return \BO\Slim\Render::withJson(
                $response,
                $validatedForm->getStatus(null, true)
            );
        }
        
        $process = static::writeReservedProcess($input, $scope);
        $process = static::writeConfirmedProcess($process);
        $queryParams = ('confirmed' == $process->getStatus()) ? 
            ['selectedprocess' => $process, 'success' => 'process_reserved'] :
            [];

        return \BO\Slim\Render::withHtml(
            $response,
            'element/helper/messageHandler.twig',
            $queryParams
        );
    }

    public static function writeReservedProcess($input, $scope)
    {
        $process = new \BO\Zmsentities\Process();

        $selectedTime = str_replace('-', ':', $input['selectedtime']);
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i', $input['selecteddate'] .' '. $selectedTime);
        
        $process->withUpdatedData($input, $dateTime, $scope);
        $process = \App::$http
            ->readPostResult('/process/status/reserved/', $process, [
                'slotType' => 'intern',
                'clientkey' => \App::CLIENTKEY,
                'slotsRequired' => (1 < $input['slotCount']) ? $input['slotCount'] : 0
            ])
            ->getEntity();

        if (isset($input['selectedprocess'])) {
            \App::$http->readDeleteResult(
                '/process/'. $input['selectedprocess'] .'/', 
                ['initiator' => $input['initiator']]
            )->getEntity();
        }
        return $process;
    }

    public static function writeConfirmedProcess($process)
    {
        $confirmedProcess = \App::$http->readPostResult('/process/status/confirmed/', $process)->getEntity();
        if ('confirmed' == $confirmedProcess->getStatus()) {
            Helper\AppointmentFormHelper::updateMailAndNotification($input, $process);
            $process = $confirmedProcess;
        }
        return $process;
    }
}
