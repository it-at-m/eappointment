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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $selectedTime = $validator->getParameter('selectedtime')->isString()->getValue();
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $selectedProcess = ($selectedProcessId) ?
            \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity() : null;
        $selectedScopeId = $validator->getParameter('selectedscope')->isNumber()->getValue();
        if ($selectedProcess) {
            $selectedScopeId = $selectedProcess->getFirstAppointment()->getScope()->getId();
        }
        $preferedScope = Helper\AppointmentFormHelper::readPreferedScope($request, $selectedScopeId, $workstation);
        $requestList = (new Helper\ClusterHelper($workstation))->getRequestList();
        $department = \App::$http->readGetResult('/scope/' . $workstation->scope['id'] . '/department/')->getEntity();
        $cluster = (new Helper\ClusterHelper($workstation))->getEntity();
        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/form.twig',
            array(
                'workstation' => $workstation,
                'scope' => $workstation->scope,
                'selectedScopeId' => $selectedScopeId,
                'preferedScope' => $preferedScope,
                'cluster' => $cluster,
                'department' => $department,
                'selectedProcess' => $selectedProcess,
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'selectedTime' => ($selectedTime) ? $selectedTime : null,
                'requestList' => (count($requestList)) ? $requestList->sortByName() : null,
                'isNew' => ($validator->getParameter('new')->isNumber()->getValue() == 1)
            )
        );
    }
}
