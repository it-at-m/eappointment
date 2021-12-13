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

        $process = $this->writeUpdatedProcess($input, $process, $validator);
        $conflictList = ($process->isWithAppointment()) ?
            $this->getConflictList($scope->getId(), $process->getFirstAppointment()) :
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

    protected function getConflictList($scopeId, $appointment)
    {
        $conflictList = \App::$http
            ->readGetResult('/scope/' . $scopeId . '/conflict/', [
                'startDate' => $appointment->getStartTime()->format('Y-m-d'),
                'endDate' => $appointment->getEndTime()->format('Y-m-d')
            ])
            ->getCollection();
            
        return ($conflictList && $conflictList->count()) ?
            $conflictList
                ->withInAppointmentSlots($appointment)
                ->sortByAppointmentDate()
                ->toConflictListByDay()[$appointment->getStartTime()->format('Y-m-d')] :
            null;
    }

    protected function writeUpdatedProcess($input, Entity $process, $validator)
    {
        $initiator = $validator->getParameter('initiator')->isString()->getValue();
        $process = \App::$http->readPostResult(
            '/process/'. $process->id .'/'. $process->authKey .'/',
            $process,
            [
                'initiator' => $initiator,
                'slotType' => 'intern',
                'clientkey' => \App::CLIENTKEY,
                'slotsRequired' => (isset($input['slotCount']) && 1 < $input['slotCount']) ? $input['slotCount'] : 0
            ]
        )->getEntity();
        AppointmentFormHelper::updateMailAndNotification($input, $process);
        return $process;
    }
}
