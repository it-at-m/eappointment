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
        $dateTime = ($selectedDate) ?
            \DateTimeImmutable::createFromFormat('Y-m-d H:i', $selectedDate .' 00:00') :
            \App::$now;

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

        $result = Helper\AppointmentFormHelper::writeQueuedProcess($request->getParsedBody(), $workstation, $dateTime);
        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/waitingnumber.twig',
            array(
                'process' => $result,
                'selectedDate' => $selectedDate
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
