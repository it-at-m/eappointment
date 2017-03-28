<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class CounterAppointmentTimes extends BaseController
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
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();

        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $workstationInfo = Helper\WorkstationInfo::getInfoBoxData($workstation, $selectedDate);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/times.twig',
            array(
                'workstation' => $workstation,
                'workstationInfo' => $workstationInfo
            )
        );
    }
}
