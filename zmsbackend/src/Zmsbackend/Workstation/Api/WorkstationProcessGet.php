<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Workstation\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Process\Service\Process;

class WorkstationProcessGet extends \BO\Zmsbackend\Api\BaseController
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
        $workstation = (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();
        $query = new \BO\Zmsbackend\Process\Service\Process();
        $processId = $args['id'];

        $process = $query->readEntity($processId, (new \BO\Zmsbackend\Helper\NoAuth()));

        if (! $process || ! $process->hasId()) {
            $exception = new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
            $exception->data = ['processId' => $processId];
            throw $exception;
        }

        $this->validateProcessStatus($process);

        $cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readByScopeId(scopeId: $workstation->scope['id'], resolveReferences: 1);
        $workstation->validateProcessScopeAccess($workstation->getScopeList($cluster), $process);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function validateProcessStatus($process)
    {
        $blockedStatuses = ['reserved', 'preconfirmed', 'deleted', 'called', 'processing'];

        if (in_array($process->getStatus(), $blockedStatuses)) {
            $exception = new \BO\Zmsbackend\Process\Exception\ProcessNotCallable();
            $exception->data = [
                'processId' => $process->getId(),
                'status' => $process->getStatus()
            ];
            throw $exception;
        }
    }
}
