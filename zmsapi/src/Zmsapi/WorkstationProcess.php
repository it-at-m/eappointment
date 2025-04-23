<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Log;
use BO\Zmsdb\Workstation;
use BO\Zmsdb\Process;
use BO\Zmsdb\Process as Query;

/**
 * @SuppressWarnings(Coupling)
 */
class WorkstationProcess extends BaseController
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
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $workstation = (new Helper\User($request, 1))->checkRights();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $allowClusterWideCall = Validator::param('allowClusterWideCall')->isBool()->setDefault(true)->getValue();
        if ($workstation->process && $workstation->process->hasId() && $workstation->process->getId() != $input['id']) {
            $exception = new Exception\Workstation\WorkstationHasAssignedProcess();
            $exception->data = ['process' => $workstation->process];
            throw $exception;
        }

        $entity = new \BO\Zmsentities\Process($input);
        $entity->testValid();
        $this->testProcessData($entity);
        $process = (new Query())->readEntity($entity['id'], $entity['authKey'], 1);
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

        $workstation->process = (new Workstation())->writeAssignedProcess($workstation, $process, \App::$now);

        $message = Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessData($entity)
    {
        $authCheck = (new Query())->readAuthKeyByProcessId($entity->id);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $entity->authKey && $authCheck['authName'] != $entity->authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
        Helper\Matching::testCurrentScopeHasRequest($entity);
    }

    protected function testProcess($process, $workstation, $allowClusterWideCall)
    {
        if ('called' == $process->status || 'processing' == $process->status) {
            throw new Exception\Process\ProcessAlreadyCalled();
        }
        if ('reserved' == $process->getStatus()) {
            throw new Exception\Process\ProcessReservedNotCallable();
        }
        if (! $allowClusterWideCall) {
            $workstation->testMatchingProcessScope($workstation->getScopeList(), $process);
        }
        $process->testValid();
    }
}
