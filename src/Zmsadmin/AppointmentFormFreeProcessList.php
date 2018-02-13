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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $selectedTime = $validator->getParameter('selectedtime')->isString()->getValue();
        $selectedScopeId = $validator->getParameter('selectedscope')->isNumber()->getValue();
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $selectedProcess = ($selectedProcessId) ?
            \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity() : null;
        $scope = Helper\AppointmentFormHelper::readPreferedScope($request, $selectedScopeId, $workstation);
        $freeProcessList = Helper\AppointmentFormHelper::readFreeProcessList($request, $workstation, $scope);
        if ($freeProcessList && $selectedProcess &&
            $selectedDate == $selectedProcess->getFirstAppointment()->toDateTime()->format('Y-m-d')
          ) {
            $entity = new \BO\Zmsentities\Process();
            $entity->appointments->addEntity($selectedProcess->getFirstAppointment());
            $freeProcessList->addEntity($entity);
        }
        $freeProcessList = ($freeProcessList) ? $freeProcessList->toProcessListByTime()->sortByTimeKey() : null;

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/freeProcessList.twig',
            array(
                'selectedDate' => $selectedDate,
                'selectedTime' => $selectedTime,
                'freeProcessList' => $freeProcessList,
                'selectedScope' => $scope,
                'selectedProcess' => $selectedProcess
            )
        );
    }
}
