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
        $requeue = Validator::param('requeue')->isNumber()->setDefault(0)->getValue();
        $skipToNext = Validator::param('skipNext')->isNumber()->setDefault(0)->getValue();

        $process = (new Query())->readEntity($workstation->process['id'], $workstation->process['authKey'], 1);
        $previousStatus = $process->status;

        if (1 === $requeue) {
            $nowTs = \App::$now->getTimestamp();
            $process->status = Process::STATUS_QUEUED;
            $process->queue['callCount'] = 0;
            $process->queue['lastCallTime'] = 0;
            $process->queue['callTime'] = $nowTs;
            $process->queue['arrivalTime'] = $nowTs;
            $process->queue['waitingTime'] = 0;
            $process->queue['wayTime'] = 0;
            $process['showUpTime'] = null;
            $process['timeoutTime'] = null;
        } elseif (1 === $skipToNext) {
            $process->setWasMissed(true);
        } elseif (
            'called' == $process->status
            && $process->queue['callCount'] > $workstation->scope->getPreference('queue', 'callCountMax')
        ) {
            $process->setWasMissed(true);
        } else {
            $process->setStatusBySettings();
        }

        $process = (new Query())->updateEntity(
            $process,
            \App::$now,
            0,
            $previousStatus,
            $workstation->getUseraccount()
        );

        $workstation->process = $process;
        (new Workstation())->writeRemovedProcess($workstation);
        unset($workstation->process);

        $message = Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
