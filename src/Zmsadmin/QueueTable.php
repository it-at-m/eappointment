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
        $clusterHelper = (new Helper\ClusterHelper($workstation));
        $processList = $clusterHelper->getProcessList($selectedDate);
        $queueList = new QueueList();
        $queueListMissed = new QueueList();
        if ($processList) {
            $queueList = $this->toProcessListByStatus($processList, $selectedDate, ['confirmed', 'queued', 'reserved']);
            $queueListMissed = $this->toProcessListByStatus($processList, $selectedDate, ['missed']);
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'block/queue/table.twig',
            array(
                'workstation' => $workstation->getArrayCopy(),
                'department' => $department,
                'source' => $workstation->getVariantName(),
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'cluster' => $clusterHelper->getEntity(),
                'processList' => $queueList,
                'processListMissed' => $queueListMissed,
                //'debug' => \App::DEBUG
            )
        );
    }

    protected function toProcessListByStatus($processList, $selectedDate, $status)
    {
        $selectedDateTime = new \DateTimeImmutable($selectedDate);
        return $processList->toQueueList($selectedDateTime)->withStatus($status)->toProcessList()->sortByArrivalTime();
    }
}
