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

        $this->validateProcessStatus($process);

        $cluster = (new \BO\Zmsdb\Cluster())->readByScopeId(scopeId: $workstation->scope['id'], resolveReferences: 1);
        $workstation->validateProcessScopeAccess($workstation->getScopeList($cluster), $process);

        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function validateProcessStatus($process)
    {
        $blockedStatuses = ['reserved', 'preconfirmed', 'deleted', 'called', 'processing'];

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
