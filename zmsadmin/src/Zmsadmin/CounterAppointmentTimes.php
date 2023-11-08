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
        $dateTime = new \BO\Zmsentities\Helper\DateTime($selectedDate);
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 0])->getEntity();
        
        try {
            $availabilityList = \App::$http
            ->readGetResult('/scope/'. $workstation->scope['id'] . '/availability/', [
                'startDate' => $selectedDate,
                'endDate' => $selectedDate
            ], \App::CONFIG_SECURE_TOKEN)
            ->getCollection()
            ->withDateTime($dateTime);
        } catch (\Exception $e) {
            $availabilityList = [];
        }

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
