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

        $process = $this->readSelectedProcessWithWaitingnumber($validator);
        if ($process instanceof \BO\Zmsentities\Process) {
            return \BO\Slim\Render::withHtml(
                $response,
                'page/printWaitingNumber.twig',
                array(
                    'title' => 'Wartenummer drucken',
                    'process' => $process,
                    'currentDate' => \App::$now
                )
            );
        }

        $process = Helper\AppointmentFormHelper::writeQueuedProcess($request, $workstation, \App::$now);
        return \BO\Slim\Render::redirect(
            'appointment_form',
            array(),
            array(
                'selectedprocess' => $process->getId(),
                'success' => 'process_queued'
            )
        );
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
}
