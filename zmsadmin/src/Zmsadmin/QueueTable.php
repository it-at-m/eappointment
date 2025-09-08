<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsadmin\Helper\ClusterHelper;
use BO\Zmsentities\Collection\QueueList;

class QueueTable extends BaseController
{
    protected $processStatusList = ['preconfirmed', 'confirmed', 'queued', 'reserved', 'deleted'];

    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        // parameters
        $validator = $request->getAttribute('validator');
        $success = $validator->getParameter('success')->isString()->getValue();
        $withCalledList = $validator->getParameter('withCalled')->isBool()->getValue();
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $selectedDateTime = $selectedDate ? new \DateTimeImmutable($selectedDate) : \App::$now;
        $selectedDateTime = ($selectedDateTime < \App::$now) ? \App::$now : $selectedDateTime;

        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();

        // HTTP requests
        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 1,
            'gql' => Helper\GraphDefaults::getWorkstation()
        ])->getEntity();
        $workstationRequest = new \BO\Zmsclient\WorkstationRequests(\App::$http, $workstation);
        $department = $workstationRequest->readDepartment();
        $processList = $workstationRequest->readProcessListByDate(
            $selectedDateTime,
            Helper\GraphDefaults::getProcess()
        );
        $changedProcess = ($selectedProcessId)
            ? \App::$http->readGetResult('/process/' . $selectedProcessId . '/', [
                'gql' => Helper\GraphDefaults::getProcess()
            ])->getEntity()
            : null;

        // data refinement
        $queueList = $processList->toQueueList(\App::$now);
        $queueList = $queueList->withSortedArrival();
        $scope = $workstation->getScope();
        $clusterHelper = (new ClusterHelper($workstation));

        $workstationGhostCount = $scope->status['queue']['ghostWorkstationCount'];
        $workstationList = ($clusterHelper->isClusterEnabled()) ?
            static::getWorkstationsByCluster($clusterHelper->getEntity()->getId()) :
            static::getWorkstationsByScope($scope->getId());

        $workstationCount = $workstationGhostCount > 0
            ? $workstationGhostCount
            : ($workstationList === null ? 1 : count($workstationList));
        $timeAverage = $scope->getPreference('queue', 'processingTimeAverage') ?? 10;

        $queueListVisible = $queueList
            ->withStatus(['preconfirmed', 'confirmed', 'queued', 'reserved', 'deleted'])
            ->withEstimatedWaitingTime($timeAverage, $workstationCount, \App::$now, false);
        $queueListMissed = $queueList->withStatus(['missed']);
        $queueListParked = $queueList->withStatus(['parked']);
        $queueListFinished = $queueList->withStatus(['finished']);

        $queueListCalled = $withCalledList ? (\App::$http
            ->readGetResult(
                '/useraccount/queue/',
                [
                    'resolveReferences' => 2,
                    'status' => 'called,processing',
                ]
            )
            ->getCollection() ?? []) : [];

        if ($queueListCalled instanceof \BO\Zmsentities\Collection\QueueList) {
            $queueListCalled->uasort(function ($queueA, $queueB) {
                $statusOrder = ['called' => 0, 'processing' => 1];

                $statusValueA = $statusOrder[$queueA->status] ?? PHP_INT_MAX;
                $statusValueB = $statusOrder[$queueB->status] ?? PHP_INT_MAX;

                $cmp = $statusValueA <=> $statusValueB;
                return $cmp !== 0 ? $cmp : $queueB->callTime <=> $queueA->callTime;
            });
        } else {
            $queueListCalled = [];
        }

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

    public static function getWorkstationsByScope($scopeId)
    {
        return \App::$http
            ->readGetResult('/scope/' . $scopeId . '/workstation/', ['resolveReferences' => 1])
            ->getCollection();
    }

    public static function getWorkstationsByCluster($clusterId)
    {
        return \App::$http
            ->readGetResult('/cluster/' . $clusterId . '/workstation/', ['resolveReferences' => 1])
            ->getCollection();
    }
}
