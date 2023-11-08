<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class AppointmentFormFreeProcessList extends BaseController
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
            \App::$http->readGetResult('/process/'. $selectedProcessId .'/', [
                'gql' => Helper\GraphDefaults::getProcess()
            ])->getEntity() : null;
        $freeProcessList = Helper\AppointmentFormHelper::readFreeProcessList($request, $workstation);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/freeProcessList.twig',
            array(
                'selectedDate' => $selectedDate,
                'selectedTime' => $selectedTime,
                'freeProcessList' => $freeProcessList,
                'selectedProcess' => $selectedProcess
            )
        );
    }
}
