<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

use \BO\Zmsentities\Collection\ProcessList;

use \BO\Zmsentities\Collection\QueueList;

class QueueTable extends BaseController
{
    protected $processStatusList = ['confirmed', 'queued', 'reserved', 'deleted'];

    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();
        $validator = $request->getAttribute('validator');
        $success = $validator->getParameter('success')->isString()->getValue();
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $clusterHelper = (new Helper\ClusterHelper($workstation));
        $processList = $clusterHelper->getProcessList($selectedDate);
        $queueList = new QueueList();
        $queueListMissed = new QueueList();
        if ($processList) {
            $queueList = $this->toProcessListByStatus($processList, $selectedDate, $this->processStatusList);
            $queueListMissed = $this->toProcessListByStatus($processList, $selectedDate, ['missed']);
        }
        $changedProcess = ($selectedProcessId)
          ? \App::$http->readGetResult('/process/'. $selectedProcessId .'/')->getEntity()
          : null;

        return \BO\Slim\Render::withHtml(
            $response,
            'block/queue/table.twig',
            array(
                'workstation' => $workstation->getArrayCopy(),
                'department' => $department,
                'source' => $workstation->getVariantName(),
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'cluster' => $clusterHelper->getEntity(),
                'clusterEnabled' => $clusterHelper->isClusterEnabled(),
                'processList' => $queueList,
                'processListMissed' => $queueListMissed,
                'changedProcess' => $changedProcess,
                'success' => $success
                //'debug' => \App::DEBUG
            )
        );
    }

    protected function toProcessListByStatus($processList, $selectedDate, $status)
    {
        $selectedDateTime = new \DateTimeImmutable($selectedDate);
        return $processList
            ->toQueueList($selectedDateTime)
            ->withStatus($status)
            ->toProcessList()
            ->sortByArrivalTime()
            ->sortByEstimatedWaitingTime();
    }
}
