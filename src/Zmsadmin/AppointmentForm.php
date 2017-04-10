<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

class AppointmentForm extends BaseController
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

        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $selectedProcess = ($selectedProcessId) ?
            \App::$http->readGetResult('/workstation/process/'. $selectedProcessId .'/get/')->getEntity() : null;

        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
            $requestList = \App::$http
                ->readGetResult('/cluster/'. $cluster->id .'/request/')->getCollection();
        } else {
            $cluster = null;
            $requestList = \App::$http
                ->readGetResult('/scope/'. $workstation->scope['id'] .'/request/')->getCollection();
        }

        $freeProcessList = Helper\AppointmentFormHelper::readFreeProcessList($request, $workstation);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/form.twig',
            array(
                'workstation' => $workstation,
                'selectedProcess' => $selectedProcess,
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'selectedTime' => ($selectedTime) ? $selectedTime : null,
                'requestList' => (count($requestList)) ? $requestList->sortByName() : null,
                'freeProcessList' => $freeProcessList,
            )
        );
    }
}
