<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Workstation;
use BO\Zmsdb\Process as Query;
use BO\Zmsentities\Process;

class WorkstationProcessDelete extends BaseController
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
        $workstation = (new Helper\User($request, 2))->checkRights();
        if (! $workstation->process['id']) {
            throw new Exception\Process\ProcessNotFound();
        }
        $process = (new Query())->readEntity($workstation->process['id'], $workstation->process['authKey'], 1);
        if ('called' == $workstation->process->status && $workstation->process->queue['callCount'] > $workstation->scope->getPreference('queue', 'callCountMax')) {
            $process->setWasMissed(true);
        }
        $process = (new Query())->updateEntity(
            $process,
            \App::$now,
            0,
            $process->status,
            $workstation->getUseraccount()
        );
        $workstation->process->setStatusBySettings();
        (new Workstation())->writeRemovedProcess($workstation);
        unset($workstation->process);

        $message = Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
