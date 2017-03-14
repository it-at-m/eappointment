<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

class CounterAppointmentForm extends BaseController
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
        $calendar = new Helper\Calendar($selectedDate);
        $freeProcessList = $calendar->readAvailableSlotsFromDayAndScope(new Scope($workstation->scope));

        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
            $requestList = \App::$http
                ->readGetResult('/cluster/'. $cluster->id .'/request/')->getCollection();
        } else {
            $requestList = \App::$http
                ->readGetResult('/scope/'. $workstation->scope['id'] .'/request/')->getCollection();
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/form.twig',
            array(
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'freeProcessList' => ($freeProcessList) ?
                    $freeProcessList->toProcessListByTime()->sortByTimeKey() :
                    null,
                'requestList' => $requestList->sortByName()
            )
        );
    }
}
