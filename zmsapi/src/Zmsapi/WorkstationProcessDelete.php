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
                throw new Exception\Process\ProcessInvalid();
            }
        }
        $isRequeuePreCall = $action === 'requeue_pre_call';
        $isRequeueCalled = $action === 'requeue_after_call_count_keep';
        $isRequeueDecrementCalled = $action === 'requeue_after_call_count_decrement';
        $isRequeueAndSkipToNext = $action === 'requeue_and_skip_to_next';

        $process = (new Query())->readEntity($workstation->process['id'], $workstation->process['authKey'], 1);
        $previousStatus = $process->status;

        error_log("********************************************************");
        if ($isRequeuePreCall) {
            $nowTs = \App::$now->getTimestamp();
            $process->status = Process::STATUS_QUEUED;
            $process['status'] = Process::STATUS_QUEUED;
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
            error_log('requeue_pre_call');
        } elseif ($isRequeueCalled && $process->queue['callCount'] > $workstation->scope->getPreference('queue', 'callCountMax')) {
            $process->setWasMissed(true);
            error_log('requeue_called');
        } elseif ($isRequeueDecrementCalled) {
            $process->status = Process::STATUS_QUEUED;
            $process['status'] = Process::STATUS_QUEUED;
            $process->wasMissed = false;
            $process['wasMissed'] = false;
            $process->queue['callCount'] = max(0, (int) $process->queue['callCount'] - 1);
            if ((int) $process->queue['callCount'] === 0) {
                $process->queue['callTime'] = 0;
                $process->queue['lastCallTime'] = 0;
            }
            $process['showUpTime'] = null;
            $process['timeoutTime'] = null;
            error_log('requeue_decrement_called');
        } elseif ($isRequeueAndSkipToNext) {
            $process->setWasMissed(true);
            error_log('requeue_and_skip_to_next');
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
        (new Workstation())->writeRemovedProcess($workstation);
        unset($workstation->process);

        $message = Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
