<?php
/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Mellon\Condition;
use BO\Slim\Render;
use BO\Zmsentities\Validator\ProcessValidator;
use BO\Zmsentities\Process as Entity;
use BO\Zmsadmin\Helper\AppointmentFormHelper;

/**
 * Update a process
 */
class ProcessSave extends BaseController
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
        $input = $request->getParams();
       
        $scope = Helper\AppointmentFormHelper::readSelectedScope($request, $workstation);
        $processId = $validator->value($args['id'])->isNumber()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/')->getEntity();
        $dateTime = ($process->isWithAppointment()) ?
            (new \DateTime())->setTimestamp($process->getFirstAppointment()->date) :
            \App::$now;
        $shouldNotify = $this->shouldSendNotifications($input, $process);
        $process->withUpdatedData($input, $dateTime, $scope);
        
        $validatedForm = ($process->isWithAppointment()) ?
            ProcessReserve::getValidatedForm($validator, $process) :
            ProcessQueue::getValidatedForm($validator, $process);

        if ($validatedForm['failed']) {
            return \BO\Slim\Render::withJson(
                $response,
                $validatedForm
            );
        }

        $process = $this->writeUpdatedProcess(
            $input,
            $process,
            $validator,
            $shouldNotify
        );
        $appointment = $process->getFirstAppointment();
        $conflictList = ($process->isWithAppointment()) ?
            static::getConflictList($scope->getId(), $appointment) :
            null;
        return \BO\Slim\Render::withHtml(
            $response,
            'element/helper/messageHandler.twig',
            array(
                'selectedprocess' => $process,
                'success' => $this->getSuccessMessage($process),
                'conflictlist' => $conflictList
            )
        );
    }

    protected function getSuccessMessage(Entity $process)
    {
        return ($process->isWithAppointment()) ? 'process_updated' : 'process_withoutappointment_updated';
    }

    public static function getConflictList($scopeId, $appointment)
    {
        $conflictList = ScopeAvailabilityDay::readConflictList($scopeId, $appointment->getStartTime());
        $conflictList = ($conflictList && $conflictList->count()) ?
            $conflictList
                ->withTimeRangeByAppointment($appointment)
                ->setConflictAmendment()
                ->toConflictListByDay() :
            null;
        return (isset($conflictList)) ? $conflictList[$appointment->getStartTime()->format('Y-m-d')] : null;
    }

    protected function writeUpdatedProcess($input, Entity $process, $validator, $notify = true)
    {
        $initiator = $validator->getParameter('initiator')->isString()->getValue();
        $process = \App::$http->readPostResult(
            '/process/'. $process->id .'/'. $process->authKey .'/',
            $process,
            [
                'initiator' => $initiator,
                'slotType' => 'intern',
                'slotsRequired' => (isset($input['slotCount']) && 1 < $input['slotCount']) ? $input['slotCount'] : 0
            ]
        )->getEntity();

        if ($notify) {
            AppointmentFormHelper::updateMailAndNotification($input, $process);
        }

        return $process;
    }

    private function shouldSendNotifications($requestData, \BO\Zmsentities\Schema\Entity $process)
    {
        $requestIds = $requestData['requests'] ?? [];
        $currentRequestIds = [];
        foreach ($process->getRequests() as $request) {
            $currentRequestIds[] = $request['id'];
        }

        if (array_merge(array_diff($requestIds, $currentRequestIds), array_diff($currentRequestIds, $requestIds))) {
            return true;
        }

        if ($process->getFirstClient()['familyName'] !== $requestData['familyName']) {
            return true;
        }

        $newDate = $requestData['selecteddate'] . ' '
            . str_replace('-', ':', $requestData['selectedtime']);

        if ($process->getFirstAppointment()->toDateTime()->format('Y-m-d H:i') !== $newDate) {
            return true;
        }

        return false;
    }
}
