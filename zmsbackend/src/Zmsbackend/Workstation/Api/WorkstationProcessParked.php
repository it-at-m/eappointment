<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Workstation\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Workstation\Service\Workstation;
use BO\Zmsbackend\Process\Service\Process as Query;
use BO\Zmsentities\Process;

class WorkstationProcessParked extends \BO\Zmsbackend\Api\BaseController
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
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $workstation = (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions();
        if (! $workstation->process['id']) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
        }
        $process = (new Query())->readEntity($workstation->process['id'], $workstation->process['authKey'], 1);
        $previousStatus = $process->status;
        $workstation->process->setParkedBy($workstation->name);
        $process->parkedBy = $workstation->name;
        $workstation->process->setStatus("parked");
        $process->status = \BO\Zmsentities\Process::STATUS_PARKED;
        $workstation->process->setStatusBySettings();
        $process = (new Query())->updateEntity(
            $process,
            \App::$now,
            0,
            $previousStatus,
            $workstation->getUseraccount()
        );
        \App::$log->info('Process parked', [
            'process_parked' => true,
            'process_id' => $process->id,
            'scope_id' => $process->scope['id']
        ]);
        (new \BO\Zmsbackend\Workstation\Service\Workstation())->writeRemovedProcess($workstation);
        unset($workstation->process);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
