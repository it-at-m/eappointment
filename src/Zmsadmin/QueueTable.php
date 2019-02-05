<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Scope;

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
        $scope = new Scope($workstation->scope);
        $department = \App::$http->readGetResult('/scope/'. $scope->getId() .'/department/')->getEntity();
        $validator = $request->getAttribute('validator');
        $success = $validator->getParameter('success')->isString()->getValue();
        $selectedDate = $validator->getParameter('selecteddate')->isString()->getValue();
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $clusterHelper = (new Helper\ClusterHelper($workstation));

        $queueListHelper = (new Helper\QueueListHelper($clusterHelper, $scope, $selectedDate));
        $queueList = $queueListHelper->getList();
        $queueListMissed = $queueListHelper->getMissedList();

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
                'processList' => $queueList->toProcessList(),
                'processListMissed' => $queueListMissed->toProcessList(),
                'changedProcess' => $changedProcess,
                'success' => $success,
                'debug' => \App::DEBUG,
            )
        );
    }
}
