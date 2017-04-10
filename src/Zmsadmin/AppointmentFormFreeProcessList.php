<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $selectedTime = $validator->getParameter('selectedtime')->isString()->getValue();
        $freeProcessList = Helper\AppointmentFormHelper::readFreeProcessList($request, $workstation);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/freeProcessList.twig',
            array(
                'selectedDate' => $selectedDate,
                'selectedTime' => $selectedTime,
                'freeProcessList' => $freeProcessList,
            )
        );
    }
}
