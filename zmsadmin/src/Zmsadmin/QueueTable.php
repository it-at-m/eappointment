<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsclient\WorkstationRequests;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Collection\QueueList;
use BO\Zmsentities\Useraccount;

class QueueTable extends BaseController
{
    protected $processStatusList = ['preconfirmed', 'confirmed', 'queued', 'reserved', 'deleted'];

    private const array QUEUE_VIEW_PERMISSIONS = [
        'waitingqueue',
        'parkedqueue',
        'missedqueue',
        'finishedqueue',
    ];

    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $validator = $request->getAttribute('validator');
        $success = $validator->getParameter('success')->isString()->getValue();
        $withCalledList = $validator->getParameter('withCalled')->isBool()->getValue();
        $includeWaitingClientsEffective = $validator
            ->getParameter('includeWaitingClientsEffective')
            ->isBool()
            ->getValue();
        $selectedDateTime = $this->resolveSelectedDateTime(
            $validator->getParameter('selecteddate')->isString()->getValue()
        );
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();

        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 1,
            'gql' => Helper\GraphDefaults::getWorkstation()
        ])->getEntity();
        $workstationRequest = new WorkstationRequests(\App::$http, $workstation);
        $department = $workstationRequest->readDepartment();
        $useraccount = $workstation->getUseraccount();

        $processList = $this->readProcessListForDateIfAllowed(
            $workstationRequest,
            $useraccount,
            $selectedDateTime
        );
        $changedProcess = $this->readChangedProcess($selectedProcessId);
        $queueList = $processList->toQueueList(\App::$now);
        $waitingClientsEffective = $this->readWaitingClientsEffective(
            $includeWaitingClientsEffective,
            $queueList,
            $workstationRequest,
            $useraccount,
            $selectedDateTime
        );

        $queueListVisible = $this->getQueueListByPermission(
            $queueList,
            $useraccount,
            'waitingqueue',
            $this->processStatusList
        );
        $queueListMissed = $this->getQueueListByPermission(
            $queueList,
            $useraccount,
            'missedqueue',
            ['missed']
        );
        $queueListParked = $this->getQueueListByPermission(
            $queueList,
            $useraccount,
            'parkedqueue',
            ['parked']
        );
        $queueListFinished = $this->getQueueListByPermission(
            $queueList,
            $useraccount,
            'finishedqueue',
            ['finished']
        );
        $queueListCalled = $this->readCalledQueueList($withCalledList, $useraccount);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/queue/table.twig',
            array(
                'workstation' => $workstation->getArrayCopy(),
                'department' => $department,
                'source' => $workstation->getVariantName(),
                'selectedDate' => $selectedDateTime->format('Y-m-d'),
                'cluster' => $workstationRequest->readCluster(),
                'clusterEnabled' => $workstation->isClusterEnabled(),
                'processList' => $queueListVisible->toProcessList(),
                'waitingClientsEffective' => $waitingClientsEffective,
                'processListMissed' => $queueListMissed->toProcessList(),
                'processListParked' => $queueListParked->toProcessList(),
                'processListFinished' => $queueListFinished->toProcessList(),
                'showCalledList' => $withCalledList,
                'queueListCalled' => $queueListCalled,
                'changedProcess' => $changedProcess,
                'success' => $success,
                'debug' => \App::DEBUG,
                'allowClusterWideCall' => \App::$allowClusterWideCall
            )
        );
    }

    private function resolveSelectedDateTime(?string $selectedDate): \DateTimeImmutable
    {
        $selectedDateTime = $selectedDate ? new \DateTimeImmutable($selectedDate) : \App::$now;
        return ($selectedDateTime < \App::$now) ? \App::$now : $selectedDateTime;
    }

    private function readProcessListForDateIfAllowed(
        WorkstationRequests $workstationRequest,
        Useraccount $useraccount,
        \DateTimeInterface $selectedDateTime
    ): ProcessList {
        if (! $useraccount->hasAnyPermission(self::QUEUE_VIEW_PERMISSIONS)) {
            return new ProcessList();
        }

        return $workstationRequest->readProcessListByDate(
            $selectedDateTime,
            Helper\GraphDefaults::getProcess()
        );
    }

    private function readChangedProcess(?int $selectedProcessId)
    {
        if (! $selectedProcessId) {
            return null;
        }

        return \App::$http->readGetResult('/process/' . $selectedProcessId . '/', [
            'gql' => Helper\GraphDefaults::getProcess()
        ])->getEntity();
    }

    private function readWaitingClientsEffective(
        ?bool $includeWaitingClientsEffective,
        QueueList $queueList,
        WorkstationRequests $workstationRequest,
        Useraccount $useraccount,
        \DateTimeInterface $selectedDateTime
    ): ?int {
        if (! $includeWaitingClientsEffective) {
            return null;
        }

        $waitingClientsQueueList = $queueList;
        if ($selectedDateTime->format('Y-m-d') !== \App::$now->format('Y-m-d')) {
            $waitingClientsQueueList = $this->readProcessListForDateIfAllowed(
                $workstationRequest,
                $useraccount,
                \App::$now
            )->toQueueList(\App::$now);
        }

        return $waitingClientsQueueList
            ->withStatus($this->processStatusList)
            ->getCountWithWaitingTime(\App::$now)
            ->count();
    }

    /**
     * @return QueueList|array
     */
    private function readCalledQueueList(?bool $withCalledList, Useraccount $useraccount)
    {
        if (! $withCalledList || ! $useraccount->hasPermissions(['openqueue'])) {
            return [];
        }

        $queueListCalled = \App::$http
            ->readGetResult(
                '/useraccount/queue/',
                [
                    'resolveReferences' => 2,
                    'status' => 'called,processing',
                ]
            )
            ->getCollection() ?? [];

        if (! ($queueListCalled instanceof QueueList)) {
            return [];
        }

        $queueListCalled->uasort(function ($queueA, $queueB) {
            $statusOrder = ['called' => 0, 'processing' => 1];

            $statusValueA = $statusOrder[$queueA->status] ?? PHP_INT_MAX;
            $statusValueB = $statusOrder[$queueB->status] ?? PHP_INT_MAX;

            $cmp = $statusValueA <=> $statusValueB;
            return $cmp !== 0 ? $cmp : $queueB->callTime <=> $queueA->callTime;
        });

        return $queueListCalled;
    }

    private function getQueueListByPermission(
        QueueList $queueList,
        Useraccount $useraccount,
        string $permission,
        array $statuses
    ): QueueList {
        if (! $useraccount->hasPermissions([$permission])) {
            return new QueueList();
        }

        return $queueList->withStatus($statuses);
    }
}
