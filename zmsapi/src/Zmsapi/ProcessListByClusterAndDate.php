<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Cluster as Query;
use BO\Zmsdb\ProcessStatusArchived;
use BO\Zmsentities\Collection\ProcessList as ProcessListCollection;
use BO\Zmsentities\Collection\QueueList;

class ProcessListByClusterAndDate extends BaseController
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
        (new Helper\User($request))->checkRights('basic');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
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
        $cluster = $query->readEntity($args['id'], 0);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }

        $queueList = new QueueList();
        foreach ($dates as $date) {
            $dateQueueList = $query->readQueueList(
                $cluster->id,
                $date,
                $resolveReferences ? $resolveReferences + 1 : 1
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
            (new ProcessStatusArchived())->readListByScopesAndDates($scopeIds, $dates);

        if ($archivedProcesses instanceof ProcessListCollection) {
            $allArchivedProcesses = $archivedProcesses;
        } else {
            error_log("Expected ProcessListCollection, received " . gettype($archivedProcesses));
        }

        $message = Response\Message::create($request);
        $message->data = $queueList->toProcessList()->withResolveLevel($resolveReferences);

        // Add all archived processes to the response data
        $message->data->addData($allArchivedProcesses);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
