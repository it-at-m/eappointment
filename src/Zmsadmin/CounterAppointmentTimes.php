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

        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 0])->getEntity();
        $availabilityList = \App::$http
            ->readGetResult('/scope/'. $workstation->scope['id'] . '/availability/', [
                'resolveReferences' => 0,
                'startDate' => $selectedDate,
                'endDate' => $selectedDate,
                'getOpeningTimes' => 1
            ])
            ->getCollection();

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/times.twig',
            array(
                'workstation' => $workstation,
                'availabilityList' => $availabilityList
            )
        );
    }
}
