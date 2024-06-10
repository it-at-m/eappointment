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
     * @SuppressWarnings(Cyclomatic)
     * @SuppressWarnings(Complexity)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 2,
            'gql' => Helper\GraphDefaults::getWorkstationWithProvider(),
        ])->getEntity();
        $selectedProcess = Helper\AppointmentFormHelper::readSelectedProcess($request);
        if ($selectedProcess && ! $workstation->hasSuperUseraccount()) {
            $workstation
                ->testMatchingProcessScope((new Helper\ClusterHelper($workstation))->getScopeList(), $selectedProcess);
        }
        
        $selectedDate = ($selectedProcess && $selectedProcess->hasId())
            ? $selectedProcess->getFirstAppointment()->toDateTime()->format('Y-m-d')
            : $validator->getParameter('selecteddate')->isString()->getValue();

        $selectedTime = ($selectedProcess && $selectedProcess->hasId())
            ? $selectedProcess->getFirstAppointment()->getStartTime()->format('H-i')
            : $validator->getParameter('selectedtime')->isString()->getValue();
        
        $selectedScope = Helper\AppointmentFormHelper::readSelectedScope($request, $workstation, $selectedProcess, 2);

        $requestList = ($selectedScope && $selectedScope->hasId())
            ? Helper\AppointmentFormHelper::readRequestList($request, $workstation, $selectedScope)
            : null;
        
        $freeProcessList = ($selectedScope)
            ? Helper\AppointmentFormHelper::readFreeProcessList($request, $workstation, 2)
            : null;
           
        $slotTimeInMinutes = null;
        if ($selectedProcess && $selectedProcess->hasId()) {
            $slotTimeInMinutes = $selectedProcess->getAppointments()->getFirst()->getAvailability()['slotTimeInMinutes'];
        } else if ($selectedScope) {
            $provider = $selectedScope->getProvider();
            $slotTimeInMinutes = $provider->getSlotTimeInMinutes();
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/form.twig',
            array(
                'workstation' => $workstation,
                'scope' => $selectedScope,
                'cluster' => (new Helper\ClusterHelper($workstation, $selectedScope))->getEntity(),
                'department' =>
                    \App::$http->readGetResult('/scope/' . $workstation->scope['id'] . '/department/', [
                        'gql' => Helper\GraphDefaults::getDepartment()
                    ])->getEntity(),
                'selectedProcess' => $selectedProcess,
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'selectedTime' => $selectedTime,
                'freeProcessList' => $freeProcessList,
                'requestList' => $requestList,
                'slotTimeInMinutes' => $slotTimeInMinutes,
            )
        );
    }
}
