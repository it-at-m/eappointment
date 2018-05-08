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
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $selectedProcess = Helper\AppointmentFormHelper::readSelectedProcess($request);

        $validator = $request->getAttribute('validator');
        $success = $validator->getParameter('success')->isString()->getValue();
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $selectedTime = $validator->getParameter('selectedtime')->isString()->getValue();
        $selectedScope = $this->getSelectedScope($validator, $workstation);

        if ($selectedProcess) {
            $selectedDate = $selectedProcess->getFirstAppointment()->toDateTime()->format('Y-m-d');
            $selectedTime = $selectedProcess->getFirstAppointment()->getStartTime()->format('H-i');
        }

        if ($request->isPost()) {
            $validatedForm = Helper\AppointmentFormHelper::handlePostRequests($request, $workstation, $selectedProcess);
            if ($validatedForm instanceof \Psr\Http\Message\ResponseInterface) {
                return $validatedForm;
            }
        }

        if ($success) {
            $changedProcess = $selectedProcess;
            $selectedProcess = null;
        }

        $requestList = (new Helper\ClusterHelper($workstation))->getRequestList();
        $freeProcessList = Helper\AppointmentFormHelper::readFreeProcessList($request, $workstation);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/form.twig',
            array(
                'workstation' => $workstation,
                'scope' => $selectedScope,
                'preferedScope' => $this->readPreferedScope($request, $workstation, $selectedProcess),
                'cluster' => (new Helper\ClusterHelper($workstation))->getEntity(),
                'department' =>
                    \App::$http->readGetResult('/scope/' . $workstation->scope['id'] . '/department/')->getEntity(),
                'selectedProcess' => $selectedProcess,
                'changedProcess' => (isset($changedProcess)) ? $changedProcess : null,
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'selectedTime' => ($selectedTime) ? $selectedTime : null,
                'requestList' => (count($requestList)) ? $requestList->sortByName() : null,
                'formData' => (isset($validatedForm) && $validatedForm) ? $validatedForm->getStatus() : null,
                'success' => $success,
                'freeProcessList' => $freeProcessList
            )
        );
    }

    protected function readPreferedScope($request, $workstation, $selectedProcess)
    {
        $validator = $request->getAttribute('validator');
        $selectedScopeId = $validator->getParameter('selectedscope')->isNumber()->getValue();
        if ($selectedProcess) {
            $selectedScopeId = $selectedProcess->getFirstAppointment()->getScope()->getId();
        }
        return Helper\AppointmentFormHelper::readPreferedScope($request, $selectedScopeId, $workstation);
    }

    protected function getSelectedScope($validator, $workstation)
    {
        $selectedScope = $validator->getParameter('selectedscope')->isNumber()->getValue();
        if ($selectedScope) {
            $selectedScope = \App::$http
              ->readGetResult('/scope/'. $selectedScope .'/', ['resolveReferences' => 1])
              ->getEntity();
        }

        return ($selectedScope && $selectedScope->hasId()) ? $selectedScope : $workstation->scope;
    }
}
