<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

use \BO\Zmsentities\Collection\ProcessList;

class QueueTable extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();
        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $cluster = $this->readCluster($workstation);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/queue/table.twig',
            array(
                'workstation' => $workstation->getArrayCopy(),
                'department' => $department,
                'source' => $workstation->getRedirect(),
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'cluster' => $cluster,
                'processList' => $this->getQueueList($workstation, $selectedDate, ['confirmed', 'queued', 'reserved']),
                'processListMissed' => $this->getQueueList($workstation, $selectedDate, ['missed'])
            )
        );
    }

    protected function readProcessList($workstation, $selectedDate)
    {
        if (1 == $workstation->queue['clusterEnabled'] && $cluster) {
            $processList = \App::$http
                ->readGetResult('/cluster/'. $cluster->id .'/process/'. $selectedDate .'/')->getCollection();
        } else {
            $processList = \App::$http
                ->readGetResult('/scope/'. $workstation->scope['id'] .'/process/'. $selectedDate .'/')->getCollection();
        }
        return $processList;
    }

    protected function getQueueList($workstation, $selectedDate, $status)
    {
        $processList = $this->readProcessList($workstation, $selectedDate);
        $selectedDateTime = new \DateTimeImmutable($selectedDate);
        $queueList = $processList
            ->toQueueList($selectedDateTime)
            ->withStatus($status)
            ->withSortedArrival();
        return $queueList->toProcessList();
    }

    // ignore cluster not found exception for queue table view
    protected function readCluster($workstation)
    {
        $cluster = null;
        try {
            $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template != 'BO\Zmsapi\Exception\Cluster\ClusterNotFound') {
                throw $exception;
            }
        }
        return $cluster;
    }
}
