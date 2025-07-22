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

        // Load process data (we need it for validation, but won't set it in response until validation passes)
        $process = $query->readEntity($processId, (new \BO\Zmsdb\Helper\NoAuth()));

        // Check if process exists
        if (! $process || ! $process->hasId()) {
            $exception = new Exception\Process\ProcessNotFound();
            $exception->data = ['processId' => $processId];
            throw $exception;
        }

        // Validate date first (before scope - fails faster for wrong dates)
        $this->validateProcessCurrentDate($process);

        // Validate scope access
        $cluster = (new \BO\Zmsdb\Cluster())->readByScopeId(scopeId: $workstation->scope['id'], resolveReferences: 1);
        $workstation->validateProcessScopeAccess($workstation->getScopeList($cluster), $process);

        // Only if ALL validations pass, create the response with process data
        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function validateProcessCurrentDate($process)
    {
        // Only check if process exists and has appointments
        if (!$process || !$process->hasId() || !$process->isWithAppointment()) {
            return;
        }

        $appointment = $process->getFirstAppointment();
        if (!$appointment || !$appointment->date) {
            return;
        }

        $now = \App::getNow();
        $today = $now->setTime(0, 0, 0);
        $appointmentDateTime = new \DateTimeImmutable();
        $appointmentDateTime = $appointmentDateTime->setTimestamp($appointment->date);
        $appointmentDate = $appointmentDateTime->setTime(0, 0, 0);

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
}
