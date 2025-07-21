<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Workstation;
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
        (new Helper\User($request))->checkRights();
        $query = new Process();
        $processId = $args['id']; // Capture process ID from URL
        $process = $query->readEntity($processId, (new \BO\Zmsdb\Helper\NoAuth()));

        // Check if the process appointment is in the future (next day and beyond)
        $this->testProcessFutureDate($process);

        if (! $process || ! $process->hasId()) {
            $exception = new Exception\Process\ProcessNotFound();
            $exception->data = ['processId' => $processId];
            throw $exception;
        }

        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessFutureDate($process)
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

        // If appointment is from tomorrow or later, throw exception
        if ($appointmentDate > $today) {
            $exception = new Exception\Process\ProcessFromFuture();
            $exception->data = [
                'processId' => $process->getId(),
                'appointmentDate' => $appointmentDateTime->format('d.m.Y'),
                'appointmentTime' => $appointmentDateTime->format('H:i')
            ];
            throw $exception;
        }
    }
}
