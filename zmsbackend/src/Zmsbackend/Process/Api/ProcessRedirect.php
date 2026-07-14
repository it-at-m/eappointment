<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Process\Service\Process;
use BO\Zmsbackend\Process\Service\Process as Query;
use BO\Zmsbackend\Process\Service\ProcessStatusQueued;
use BO\Zmsbackend\Workstation\Service\Workstation;
use BO\Zmsentities\Collection\RequestList;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessRedirect extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(\Psr\Http\Message\RequestInterface $request, \Psr\Http\Message\ResponseInterface $response, array $args)
    {
        $workstation = (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('appointment');
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        $newProcess = new \BO\Zmsentities\Process($input);
        $process = $this->readValidProcess($workstation, $entity, $input, $workstation);
        $newProcess->requests = new RequestList();
        $this->testProcessAccess($workstation, $process);
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $processStatusArchived = new \BO\Zmsbackend\Process\Service\ProcessStatusArchived();
        $process->status = 'finished';
        $process = (new Query())->updateEntity($process, \App::$now, 0, 'processing', $workstation->getUseraccount());
        (new \BO\Zmsbackend\Workstation\Service\Workstation())->writeRemovedProcess($workstation);
        $processStatusArchived->writeEntityFinished($process, \App::$now, false);
        $newProcess->displayNumber = $process->displayNumber;
        $newProcess = (new \BO\Zmsbackend\Process\Service\Process())->redirectToScope($newProcess, $process->scope, $process->queue['number'] ?? $process->id, $workstation->getUseraccount());
        \App::$log->info('Process redirected', [
            'process_redirected' => true,
            'process_id' => $newProcess->id,
            'scope_id' => $newProcess->scope['id']
        ]);
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $newProcess;
        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
    }

    protected function testProcessData($entity)
    {
        $entity->testValid();
        $authCheck = (new Query())->readAuthKeyByProcessId($entity->id);
        if (! $authCheck) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
        } elseif ($authCheck['authKey'] !== $entity->authKey) {
            throw new \BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed();
        }
    }

    protected function testProcessAccess($workstation, $process)
    {
        $cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readByScopeId($workstation->scope['id'], 1);
        $workstation->validateProcessScopeAccess($workstation->getScopeList($cluster), $process);
        if ($workstation->process && $workstation->process->hasId() && $workstation->process->id != $process->id) {
            $exception = new \BO\Zmsbackend\Workstation\Exception\WorkstationHasAssignedProcess();
            $exception->data = [
                'process' => $workstation->process
            ];
            throw $exception;
        }
    }

    protected function readValidProcess($workstation, $entity, $input)
    {
        if ($entity->hasProcessCredentials()) {
            $this->testProcessData($entity);
            $entity->addData($input);
            $process = (new Query())->updateEntity($entity, \App::$now, 0, null, $workstation->getUseraccount());
        } elseif ($entity->hasQueueNumber()) {
            // Allow waitingnumbers over 1000 with the fourth parameter
            $process = \BO\Zmsbackend\Process\Service\ProcessStatusQueued::init()
                ->readByQueueNumberAndScope($entity['queue']['number'], $workstation->scope['id'], 0, 100000000);
            if (! $process->id) {
                $entity->testValid();
                throw new \BO\Zmsbackend\Process\Exception\ProcessInvalid();
            }

            $process->testValid();
        } else {
            $entity->testValid();
            throw new \BO\Zmsbackend\Process\Exception\ProcessInvalid();
        }
        return $process;
    }
}
