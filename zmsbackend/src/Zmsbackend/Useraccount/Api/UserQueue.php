<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Useraccount\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Department\Service\Department;
use BO\Zmsentities\Collection\QueueList;
use BO\Zmsentities\Helper\DateTime;

class UserQueue extends \BO\Zmsbackend\Api\BaseController
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
        $workstation = (new \BO\Zmsbackend\Helper\User($request, 2))
            ->checkAnyPermission(
                'waitingqueue',
                'openqueue',
                'parkedqueue',
                'missedqueue',
                'finishedqueue'
            );

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $statusParameter = Validator::param('status')->isString()->getValue();
        $statuses = empty($statusParameter) ? [] : array_map('trim', explode(',', $statusParameter));
        $selectedDate = Validator::param('date')->isString()->getValue();
        $dateTime = ($selectedDate)
            ? (new DateTime($selectedDate))->modify(\App::$now->format('H:i'))
            : \App::$now;

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);

        $useraccount = $workstation->getUseraccount();
        $departmentService = new Department();
        $queueList = new QueueList();

        foreach ($useraccount['departments'] as $department) {
            $queueList->addList(
                $departmentService->readQueueList(
                    $department->id,
                    $dateTime,
                    $resolveReferences
                )
            );
        }

        $queues = $queueList->withSortedWaitingTime();

        $permissionByStatus = [
            'preconfirmed' => 'waitingqueue',
            'confirmed' => 'waitingqueue',
            'queued' => 'waitingqueue',
            'reserved' => 'waitingqueue',
            'deleted' => 'waitingqueue',
            'called' => 'openqueue',
            'processing' => 'openqueue',
            'parked' => 'parkedqueue',
            'missed' => 'missedqueue',
            'finished' => 'finishedqueue',
        ];

        $filteredQueues = [];

        foreach ($queues as $queue) {
            $requiredPermission = $permissionByStatus[$queue->status] ?? null;

            if (
                $requiredPermission === null
                || ! $useraccount->hasPermissions([$requiredPermission])
            ) {
                continue;
            }

            if (
                ! empty($statuses)
                && ! in_array($queue->status, $statuses, true)
            ) {
                continue;
            }

            if ($resolveReferences > 1) {
                $queue->process = $queue->getProcess();
            }

            $filteredQueues[] = $queue;
        }

        $message->data = $filteredQueues;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);

        return $response;
    }
}
