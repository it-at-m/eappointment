<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

class CounterQueueTable extends BaseController
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

        $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
        if (1 == $workstation->queue['clusterEnabled']) {
            $processList = \App::$http
                ->readGetResult('/cluster/'. $cluster->id .'/process/'. $selectedDate .'/')->getCollection();
        } else {
            $processList = \App::$http
                ->readGetResult('/scope/'. $workstation->scope['id'] .'/process/'. $selectedDate .'/')->getCollection();
        }

        $withAppointments = $processList->withAppointment();
        $withoutAppointments = $processList->withOutAppointment()->withSortedArrival();
        $completeList = clone $withAppointments;
        $completeList->addList($withoutAppointments);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/queue/table.twig',
            array(
                'workstation' => $workstation->getArrayCopy(),
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'cluster' => ($cluster) ? $cluster : null,
                'processListWithAppointments' => $withAppointments,
                'processListComplete' => $completeList,
            )
        );
    }
}
