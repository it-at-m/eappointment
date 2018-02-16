<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

use BO\Zmsentities\Helper\ProcessFormValidation as FormValidation;

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
        $success = $validator->getParameter('success')->isString()->getValue();
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $selectedTime = $validator->getParameter('selectedtime')->isString()->getValue();
        $selectedProcess = $this->readSelectedProcess($validator);
        if ($selectedProcess) {
            $selectedDate = $selectedProcess->getFirstAppointment()->toDateTime()->format('Y-m-d');
            $selectedTime = $selectedProcess->getFirstAppointment()->getStartTime()->format('H-i');
        }
        $preferedScope = $this->readPreferedScope($request, $workstation, $selectedProcess);
        $requestList = (new Helper\ClusterHelper($workstation))->getRequestList();
        $department = \App::$http->readGetResult('/scope/' . $workstation->scope['id'] . '/department/')->getEntity();
        $cluster = (new Helper\ClusterHelper($workstation))->getEntity();

        if ($request->isPost()) {
            return $this->handlePostRequests($request, $workstation, $selectedProcess);
        }

        if ($success) {
            $changedProcess = $selectedProcess;
            $selectedProcess = null;
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'block/appointment/form.twig',
            array(
                'workstation' => $workstation,
                'scope' => $workstation->scope,
                'preferedScope' => $preferedScope,
                'cluster' => $cluster,
                'department' => $department,
                'selectedProcess' => $selectedProcess,
                'changedProcess' => ($changedProcess) ? $changedProcess : null,
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'selectedTime' => ($selectedTime) ? $selectedTime : null,
                'requestList' => (count($requestList)) ? $requestList->sortByName() : null,
                'formData' => ($validatedForm) ? $validatedForm->getStatus() : null,
                'success' => $success
            )
        );
    }

    protected function getValidatedForm($request, $workstation)
    {
        $input = $request->getParsedBody();
        $scope = Helper\AppointmentFormHelper::readPreferedScope($request, $input['scope'], $workstation);
        $validationList = FormValidation::fromAdminParameters($scope['preferences']);
        return $validationList;
    }

    protected function readSelectedProcess($validator)
    {
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        return ($selectedProcessId) ?
            \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity() :
            null;
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

    protected function handlePostRequests($request, $workstation, $selectedProcess)
    {
        $input = $request->getParsedBody();
        $validatedForm = $this->getValidatedForm($request, $workstation);

        if (! $validatedForm->hasFailed() && isset($input['reserve'])) {
            return \BO\Slim\Render::redirect(
              'processReserve',
              array(),
              array(),
              307
          );
        }
        if (! $validatedForm->hasFailed() && isset($input['update'])) {
            return \BO\Slim\Render::redirect(
              'processSave',
              array(
                  'id' => $selectedProcess->getId()
              ),
              array(),
              307
          );
        }
        if (! $validatedForm->hasFailed() && isset($input['queue'])) {
            return \BO\Slim\Render::redirect(
              'processQueue',
              array(),
              array(),
              307
          );
        }
        if (isset($input['delete'])) {
            return \BO\Slim\Render::redirect(
              'processDelete',
              array('id' => $input['processId']),
              array()
          );
        }
    }
}
