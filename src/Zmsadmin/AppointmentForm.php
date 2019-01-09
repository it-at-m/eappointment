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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $selectedProcess = Helper\AppointmentFormHelper::readSelectedProcess($request);

        $selectedDate = ($selectedProcess)
            ? $selectedProcess->getFirstAppointment()->toDateTime()->format('Y-m-d')
            : $validator->getParameter('selecteddate')->isString()->getValue();

        $selectedTime = ($selectedProcess)
            ? $selectedProcess->getFirstAppointment()->getStartTime()->format('H-i')
            : $validator->getParameter('selectedtime')->isString()->getValue();
        
        $selectedScope = ($selectedProcess)
            ? $selectedProcess->getCurrentScope()
            : Helper\AppointmentFormHelper::readSelectedScope($request, $workstation);

        if ($request->isPost()) {
            $validatedForm = Helper\AppointmentFormHelper::handlePostRequests($request, $workstation, $selectedProcess);
            if ($validatedForm instanceof \Psr\Http\Message\ResponseInterface) {
                return $validatedForm;
            }
        }

        $requestList = ($selectedScope)
            ? Helper\AppointmentFormHelper::readRequestList($request, $workstation)
            : null;
        
        $freeProcessList = ($selectedScope)
            ? Helper\AppointmentFormHelper::readFreeProcessList($request, $workstation)
            : null;

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/form.twig',
            array(
                'workstation' => $workstation,
                'scope' => $selectedScope,
                'cluster' => (new Helper\ClusterHelper($workstation, $selectedScope))->getEntity(),
                'department' =>
                    \App::$http->readGetResult('/scope/' . $workstation->scope['id'] . '/department/')->getEntity(),
                'selectedProcess' => $selectedProcess,
                'changedProcess' =>
                    ($validator->getParameter('success')->isString()->getValue()) ? $selectedProcess : null,
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'selectedTime' => $selectedTime,
                'formData' => (isset($validatedForm) && $validatedForm) ? $validatedForm->getStatus(null, true) : null,
                'success' => $validator->getParameter('success')->isString()->getValue(),
                'error' => $validator->getParameter('error')->isString()->getValue(),
                'freeProcessList' => $freeProcessList,
                'requestList' => $requestList
            )
        );
    }
}
