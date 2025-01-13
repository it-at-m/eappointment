<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class AppointmentFormButtons extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 2,
            'gql' => Helper\GraphDefaults::getWorkstation()
        ])->getEntity();
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $selectedTime = $validator->getParameter('selectedtime')->isString()->getValue();
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $selectedProcess = ($selectedProcessId) ?
            \App::$http->readGetResult('/process/' . $selectedProcessId . '/')->getEntity() : null;

        $isNewAppointment = $this->isNewAppointment(
            $selectedProcess,
            $selectedDate,
            str_replace('-', ':', $selectedTime)
        );

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/formButtons.twig',
            array(
                'workstation' => $workstation,
                'selectedProcess' => $selectedProcess,
                'selectedTime' => $selectedTime = ($selectedTime) ? $selectedTime : '00-00',
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'isNewAppointment' => $isNewAppointment
            )
        );
    }

    protected function isNewAppointment($process, $selectedDate, $selectedTime)
    {
        $selectedAppointment = new \BO\Zmsentities\Appointment();
        $selectedAppointment->setTime($selectedDate . ' ' . $selectedTime);
        return ($process) ? ($process->getFirstAppointment()->date != $selectedAppointment->date) : false;
    }
}
