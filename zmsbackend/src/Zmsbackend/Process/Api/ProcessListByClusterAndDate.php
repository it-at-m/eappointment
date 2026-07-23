<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Cluster\Service\Cluster as Query;
use BO\Zmsbackend\Process\Service\ProcessStatusArchived;
use BO\Zmsentities\Collection\ProcessList as ProcessListCollection;
use BO\Zmsentities\Collection\QueueList;

class ProcessListByClusterAndDate extends \BO\Zmsbackend\Api\BaseController
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
        $showWeek = Validator::param('showWeek')->isNumber()->setDefault(0)->getValue();
        $dateTime = new \BO\Zmsentities\Helper\DateTime($args['date']);
        $dateTime = $dateTime->modify(\App::$now->format('H:i'));
        $dates = [$dateTime];

        if ($showWeek) {
            $dates = [];
            $startDate = clone $dateTime->modify('Monday this week');
            $endDate = clone $dateTime->modify('Sunday this week');

            while ($startDate <= $endDate) {
                $dates[] = $startDate;
                $startDate = $startDate->modify('+1 day');
            }
        }

        $query = new Query();
        $cluster = $query->readEntity($args['id'], 1);
        if (! $cluster) {
            throw new \BO\Zmsbackend\Cluster\Exception\ClusterNotFound();
        }

        $workstation = (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions(
            'appointment',
            new \BO\Zmsentities\Useraccount\EntityAccess($cluster)
        );
        $useraccount = $workstation->getUseraccount();

        $shortNames = [];
        foreach ($cluster->scopes as $scope) {
            $shortNames[$scope->id] = $scope->shortName;
        }

        $queueList = new QueueList();
        foreach ($dates as $date) {
            $dateQueueList = $query->readQueueList(
                $cluster->id,
                $date,
                2,
                ['availability', 'scope', 'scopeprovider']
            );

            if (! $dateQueueList) {
                continue;
            }

            /** @var QueueList $dateQueueList */
            $queueList->addList($dateQueueList);
        }

        $allArchivedProcesses = new ProcessListCollection();
        $scopeIds = $cluster->scopes->getIds();

        $archivedProcesses =
            (new \BO\Zmsbackend\Process\Service\ProcessStatusArchived())->readListByScopesAndDates($scopeIds, $dates);

        if ($archivedProcesses instanceof ProcessListCollection) {
            $allArchivedProcesses = $archivedProcesses;
        } else {
            \App::$log->error('Expected ProcessListCollection, received different type', [
                'received_type' => gettype($archivedProcesses),
                'cluster_id' => $args['id'],
            ]);
        }

        $processList = $queueList->toProcessList()->sortByEstimatedWaitingTime()->withResolveLevel(2);
        foreach ($processList as $process) {
            if (!$process->scope->id) {
                continue;
            }

            $process->scope->shortName = $shortNames[$process->scope->id];
        }

        $processList->addData($allArchivedProcesses);
        $processList = \BO\Zmsbackend\Helper\QueueStatusPermission::filterProcessList(
            $processList,
            $useraccount
        );

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $processList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
