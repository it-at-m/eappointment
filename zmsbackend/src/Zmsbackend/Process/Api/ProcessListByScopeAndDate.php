<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Scope\Service\Scope as Query;
use BO\Zmsbackend\Process\Service\ProcessStatusArchived;
use BO\Zmsentities\Collection\QueueList;

class ProcessListByScopeAndDate extends \BO\Zmsbackend\Api\BaseController
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
        $strictQueuePermissions = Validator::param('strictQueuePermissions')->isNumber()->setDefault(0)->getValue();
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
        $scope = $query->readWithWorkstationCount($args['id'], \App::$now, 0, ['none']);
        if (! $scope || ! $scope->getId()) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }

        $workstation = (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions(
            new \BO\Zmsentities\Useraccount\EntityAccess($scope)
        );
        $useraccount = $workstation->getUseraccount();

        $queueList = new QueueList();
        foreach ($dates as $date) {
            $queueList->addList($query->readQueueListWithWaitingTime(
                $scope,
                $date,
                2,
                ['availability', 'scope', 'scopeprovider']
            ));
        }

        $archivedProcesses =
            (new \BO\Zmsbackend\Process\Service\ProcessStatusArchived())->readListByScopesAndDates([$scope->getId()], $dates);

        $processList = $queueList->toProcessList()->sortByEstimatedWaitingTime()->withResolveLevel(2);
        $processList->addData($archivedProcesses);
        // strictQueuePermissions=1: queue UI (no appointment fallback for waiting statuses).
        // Default: appointment holders may still see waiting-pipeline for calendar/planning.
        $processList = \BO\Zmsbackend\Helper\QueueStatusPermission::filterProcessList(
            $processList,
            $useraccount,
            ! $strictQueuePermissions
        );

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $processList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
