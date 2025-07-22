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

        if (! $process || ! $process->hasId()) {
            $exception = new Exception\Process\ProcessNotFound();
            $exception->data = ['processId' => $processId];
            throw $exception;
        }

        $this->validateProcessCurrentDate($process);
        $this->validateProcessStatus($process);

        $cluster = (new \BO\Zmsdb\Cluster())->readByScopeId(scopeId: $workstation->scope['id'], resolveReferences: 1);
        $workstation->validateProcessScopeAccess($workstation->getScopeList($cluster), $process);

        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function validateProcessCurrentDate($process)
    {
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

    protected function validateProcessStatus($process)
    {
        $blockedStatuses = ['reserved', 'preconfirmed', 'deleted', 'free', 'archived', 'anonymized', 'blocked', 'called', 'processing'];

        if (in_array($process->getStatus(), $blockedStatuses)) {
            $exception = new Exception\Process\ProcessNotCallable();
            $exception->data = [
                'processId' => $process->getId(),
                'status' => $process->getStatus()
            ];
            throw $exception;
        }
    }
}
