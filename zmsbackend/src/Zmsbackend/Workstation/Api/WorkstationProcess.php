<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Workstation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Workstation\Service\Workstation;
use BO\Zmsbackend\Process\Service\Process as Query;

/**
 * @SuppressWarnings(Coupling)
 */
class WorkstationProcess extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        \BO\Zmsbackend\Connection\Select::setTransaction(true);
        \BO\Zmsbackend\Connection\Select::getWriteConnection();

        try {
            $workstation = (new \BO\Zmsbackend\Helper\User($request, 1))->checkPermissions();
            $input = Validator::input()->isJson()->assertValid()->getValue();
            $allowClusterWideCall = Validator::param('allowClusterWideCall')->isBool()->setDefault(true)->getValue();
            if ($workstation->process && $workstation->process->hasId() && $workstation->process->getId() != $input['id']) {
                $exception = new \BO\Zmsbackend\Workstation\Exception\WorkstationHasAssignedProcess();
                $exception->data = ['process' => $workstation->process];
                throw $exception;
            }

            $entity = new \BO\Zmsentities\Process($input);
            $entity->testValid();
            $this->testProcessData($entity);
            $process = (new Query())->readEntity($entity['id'], $entity['authKey'], 1);

            $this->validateProcessCurrentDate($process);

            $previousStatus = $process->status;
            $process->status = 'called';
            $process = (new Query())->updateEntity(
                $process,
                \App::$now,
                0,
                $previousStatus,
                $workstation->getUseraccount()
            );

            $process = new \BO\Zmsentities\Process($input);
            $this->testProcess($process, $workstation, $allowClusterWideCall);
            $process->setCallTime(\App::$now);
            $process->queue['callCount']++;

            $process->status = 'called';

            $workstation->process = (new \BO\Zmsbackend\Workstation\Service\Workstation())->writeAssignedProcess($workstation, $process, \App::$now);
            \BO\Zmsbackend\Connection\Select::writeCommit();
            $message = \BO\Zmsbackend\Api\Response\Message::create($request);
            $message->data = $workstation;

            $response = Render::withLastModified($response, time(), '0');
            $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
            return $response;
        } catch (\DomainException $e) {
            \BO\Zmsbackend\Connection\Select::writeRollback();

            if ($e->getMessage() === 'PROCESS_ALREADY_ASSIGNED') {
                $exception = new \BO\Zmsbackend\Process\Exception\ProcessAlreadyCalled();
                $exception->data = [
                    'processId' => $input['id'] ?? null
                ];
                throw $exception;
            }

            throw $e;
        } catch (\Throwable $e) {
            \BO\Zmsbackend\Connection\Select::writeRollback();
            throw $e;
        }
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
            $exception = new \BO\Zmsbackend\Process\Exception\ProcessNotCurrentDate();
            $exception->data = [
                'processId' => $process->getId(),
                'appointmentDate' => $appointmentDateTime->format('d.m.Y'),
                'appointmentTime' => $appointmentDateTime->format('H:i') . ' Uhr'
            ];
            throw $exception;
        }
    }

    protected function testProcessData($entity)
    {
        $authCheck = (new Query())->readAuthKeyByProcessId($entity->id);
        if (! $authCheck) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
        } elseif ($authCheck['authKey'] !== $entity->authKey) {
            throw new \BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed();
        }
        \BO\Zmsbackend\Helper\Matching::testCurrentScopeHasRequest($entity);
    }

    protected function testProcess($process, $workstation, $allowClusterWideCall)
    {
        if ('called' == $process->status || 'processing' == $process->status) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessAlreadyCalled();
        }
        if ('reserved' == $process->getStatus()) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessReservedNotCallable();
        }
        if (! $allowClusterWideCall) {
            $workstation->validateProcessScopeAccess($workstation->getScopeList(), $process);
        }
        $process->testValid();
    }
}
