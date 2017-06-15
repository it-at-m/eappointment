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
        $clusterHelper = (new Helper\ClusterHelper($workstation));
        $processList = $clusterHelper->getProcessList($selectedDate);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/queue/table.twig',
            array(
                'workstation' => $workstation->getArrayCopy(),
                'department' => $department,
                'source' => $workstation->getRedirect(),
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'cluster' => $clusterHelper->getEntity(),
                'processList' => $this->getQueueList($processList, $selectedDate, ['confirmed', 'queued', 'reserved']),
                'processListMissed' => $this->getQueueList($processList, $selectedDate, ['missed'])
            )
        );
    }

    protected function getQueueList($processList, $selectedDate, $status)
    {
        $selectedDateTime = new \DateTimeImmutable($selectedDate);
        $queueList = $processList
            ->toQueueList($selectedDateTime)
            ->withStatus($status)
            ->withSortedArrival();
        return $queueList->toProcessList();
    }
}
