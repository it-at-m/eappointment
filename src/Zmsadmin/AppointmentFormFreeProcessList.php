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

        $slotType = $validator->getParameter('slottype')->isString()->getValue();
        $slotsRequired = $validator->getParameter('slotsrequired')->isNumber()->getValue();

        $calendar = new Helper\Calendar($selectedDate);
        $cluster = (1 == $workstation->queue['clusterEnabled']) ?
            \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity() :
            null;
        $scopeList = $workstation->getScopeList($cluster);

        $freeProcessList = $calendar->readAvailableSlotsFromDayAndScopeList($scopeList, $slotType, $slotsRequired);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/freeProcessList.twig',
            array(
                'selectedDate' => $selectedDate,
                'selectedTime' => $selectedTime,
                'freeProcessList' => ($freeProcessList) ?
                    $freeProcessList->toProcessListByTime()->sortByTimeKey() :
                    null,
            )
        );
    }
}
