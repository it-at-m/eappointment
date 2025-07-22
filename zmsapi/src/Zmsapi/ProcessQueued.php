<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Process as Query;
use BO\Zmsdb\ProcessStatusQueued;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessQueued extends BaseController
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
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        $entity->testValid();
        $this->testProcessData($entity);
        $process = (new Query())->readEntity($entity['id'], $entity['authKey'], 1);
        $previousStatus = $process->status;
        if (isset($entity->queue['waitingTime'])) {
            $process->queue['waitingTime'] = $entity->queue['waitingTime'];
        }
        if (isset($entity->queue['arrivalTime'])) {
            $process->queue['arrivalTime'] = $entity->queue['arrivalTime'];
        }
        $process->status = 'queued';
        $process->queue['callCount'] = 0;
        $process->queue['lastCallTime'] = 0;
        $process->queue['priority'] = $entity->getPriority();
        $cluster = (new \BO\Zmsdb\Cluster())->readByScopeId($workstation->scope['id'], 1);
        $workstation->testMatchingProcessScope($workstation->getScopeList($cluster), $process);
        $process = (new Query())->updateEntity(
            $process,
            \App::$now,
            0,
            $previousStatus,
            $workstation->getUseraccount()
        );
        $message = Response\Message::create($request);
        $message->data = $process;

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
}
