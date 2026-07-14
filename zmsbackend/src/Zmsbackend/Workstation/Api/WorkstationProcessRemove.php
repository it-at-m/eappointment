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
use BO\Zmsentities\Process;

class WorkstationProcessRemove extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
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
        $action = (string) Validator::param('action')->isString()->setDefault('')->getValue();
        if ($action !== '') {
            $action = strtolower($action);
            if (
                !in_array($action, [
                'requeue_and_skip_to_next',
                'requeue_pre_call',
                'requeue_after_call_count_keep',
                'requeue_after_call_count_decrement',
                ], true)
            ) {
                throw new \BO\Zmsbackend\Process\Exception\ProcessInvalid();
            }
        }
        $isRequeuePreCall = $action === 'requeue_pre_call';
        $isRequeueCalled = $action === 'requeue_after_call_count_keep';
        $isRequeueDecrementCalled = $action === 'requeue_after_call_count_decrement';
        $isRequeueAndSkipToNext = $action === 'requeue_and_skip_to_next';

        $process = (new Query())->readEntity($workstation->process['id'], $workstation->process['authKey'], 1);
        $previousStatus = $process->status;

        if ($isRequeuePreCall) {
            $nowTs = \App::$now->getTimestamp();
            $process->status = \BO\Zmsentities\Process::STATUS_QUEUED;
            $process['status'] = \BO\Zmsentities\Process::STATUS_QUEUED;
            $process->wasMissed = false;
            $process['wasMissed'] = false;
            $process->queue['callCount'] = 0;
            $process->queue['lastCallTime'] = 0;
            $process->queue['callTime'] = 0;
            $process->queue['arrivalTime'] = $nowTs;
            $process->queue['waitingTime'] = 0;
            $process->queue['wayTime'] = 0;
            $process['showUpTime'] = null;
            $process['timeoutTime'] = null;
        } elseif (
            ($isRequeueCalled || $isRequeueAndSkipToNext)
            && $process->queue['callCount'] > $workstation->scope->getPreference('queue', 'callCountMax')
        ) {
            $process->setWasMissed(true);
        } elseif ($isRequeueDecrementCalled) {
            $process->status = \BO\Zmsentities\Process::STATUS_QUEUED;
            $process['status'] = \BO\Zmsentities\Process::STATUS_QUEUED;
            $process->wasMissed = false;
            $process['wasMissed'] = false;
            $process->queue['callCount'] = max(0, (int) $process->queue['callCount'] - 1);
            if ((int) $process->queue['callCount'] === 0) {
                $process->queue['callTime'] = 0;
                $process->queue['lastCallTime'] = 0;
            }
            $process['showUpTime'] = null;
            $process['timeoutTime'] = null;
        }

        $process = (new Query())->updateEntity(
            $process,
            \App::$now,
            0,
            $previousStatus,
            $workstation->getUseraccount()
        );

        $workstation->process->setStatusBySettings();

        $workstation->process = $process;
        (new \BO\Zmsbackend\Workstation\Service\Workstation())->writeRemovedProcess($workstation);
        unset($workstation->process);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
