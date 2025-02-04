<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Collection\QueueList;

class QueueTable extends BaseController
{
    protected $processStatusList = ['preconfirmed','confirmed', 'queued', 'reserved', 'deleted'];

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
          ? \App::$http->readGetResult('/process/'. $selectedProcessId .'/', [
            'gql' => Helper\GraphDefaults::getProcess()
          ])->getEntity()
          : null;

        // data refinement
        $queueList = $processList->toQueueList(\App::$now);
        $queueList->uasort(function ($queueA, $queueB) {
            return $queueA->arrivalTime - $queueB->arrivalTime;
        });
        $queueListVisible = $queueList->withStatus(['preconfirmed', 'confirmed', 'queued', 'reserved', 'deleted']);
        $queueListMissed = $queueList->withStatus(['missed']);
        $queueListParked = $queueList->withStatus(['parked']);
        $queueListFinished = $queueList->withStatus(['finished']);

        $queueListCalled = $withCalledList ? (\App::$http
            ->readGetResult(
                '/useraccount/queue/',
                [
                    'resolveReferences' => 2,
                    'status' => 'called',
                ]
            )
            ->getCollection() ?? []) : false;

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
}
