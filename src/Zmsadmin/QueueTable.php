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
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();

        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();

        $processList = new ProcessList();
        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
            $resultList = \App::$http
                ->readGetResult('/cluster/'. $cluster->id .'/process/'. $selectedDate .'/')->getCollection();
        } else {
            $resultList = \App::$http
                ->readGetResult('/scope/'. $workstation->scope['id'] .'/process/'. $selectedDate .'/')->getCollection();
        }
        $processList = ($resultList) ? $resultList : $processList;

        $selectedDateTime = new \DateTimeImmutable($selectedDate);
        $queueList = $processList
            ->toQueueList($selectedDateTime)
            ->withStatus(array('confirmed', 'queued', 'reserved'))
            ->withSortedArrival();

        $queueListMissed = $processList
            ->toQueueList($selectedDateTime)
            ->withStatus(array('missed'))
            ->withSortedArrival();

        return \BO\Slim\Render::withHtml(
            $response,
            'block/queue/table.twig',
            array(
                'workstation' => $workstation->getArrayCopy(),
                'selectedDate' => ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d'),
                'cluster' => ($cluster) ? $cluster : null,
                'processList' => $queueList->toProcessList(),
                'processListMissed' => $queueListMissed->toProcessList()
            )
        );
    }
}
