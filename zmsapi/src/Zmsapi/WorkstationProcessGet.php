<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Process;

class WorkstationProcessGet extends BaseController
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
        $workstation = (new Helper\User($request))->checkRights();
        $query = new Process();
        $processId = $args['id'];
        $process = $query->readEntity($processId, (new \BO\Zmsdb\Helper\NoAuth()));

        error_log(json_encode($process));
        // Check if the process appointment is not from the current date (past or future)
        $this->testProcessCurrentDate($process);

        if (! $process || ! $process->hasId()) {
            $exception = new Exception\Process\ProcessNotFound();
            $exception->data = ['processId' => $processId];
            throw $exception;
        }

        $this->testProcessScopeAccess($workstation, $process);

        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessCurrentDate($process)
    {
        // Only check if process exists and has appointments
        if (!$process || !$process->hasId() || !$process->isWithAppointment()) {
            return;
        }

        $appointment = $process->getFirstAppointment();
        if (!$appointment || !$appointment->date) {
            return;
        }

        // Get current date (start of today) and appointment date (start of appointment day)
        $now = \App::getNow();
        $today = $now->setTime(0, 0, 0);
        $appointmentDateTime = new \DateTimeImmutable();
        $appointmentDateTime = $appointmentDateTime->setTimestamp($appointment->date);
        $appointmentDate = $appointmentDateTime->setTime(0, 0, 0);

        // If appointment is NOT from today (either past or future)
        if ($appointmentDate != $today) {
            $exception = new Exception\Process\ProcessNotCurrentDate();
            $exception->data = [
                'processId' => $process->getId(),
                'appointmentDate' => $appointmentDateTime->format('d.m.Y'),
                'appointmentTime' => $appointmentDateTime->format('H:i') . ' Uhr'
            ];
            throw $exception;
        }
    }

    protected function testProcessScopeAccess($workstation, $process)
    {
        // Get cluster and scope list for this workstation
        $cluster = (new \BO\Zmsdb\Cluster())->readByScopeId($workstation->scope['id'], 1);

        try {
            // Use the same validation method as other controllers
            $workstation->testMatchingProcessScope($workstation->getScopeList($cluster), $process);
        } catch (\BO\Zmsentities\Exception\WorkstationProcessMatchScopeFailed $exception) {
            // Add process data to the exception for better error display
            $exception->data = $process;
            throw $exception;
        }
    }
}
