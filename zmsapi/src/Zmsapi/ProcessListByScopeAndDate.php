<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;
use \BO\Zmsdb\ProcessStatusArchived;
use BO\Zmsentities\Collection\QueueList;

class ProcessListByScopeAndDate extends BaseController
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
        $scope = $query->readWithWorkstationCount($args['id'], \App::$now, 0);
        if (! $scope || ! $scope->getId()) {
            throw new Exception\Scope\ScopeNotFound();
        }

        $queueList = new QueueList();
        foreach ($dates as $date) {
            $queueList->addList($query->readQueueListWithWaitingTime(
                $scope,
                $date,
                $resolveReferences? $resolveReferences + 1 : 1 // resolveReferences is for process, for queue we have to +1
            ));
        }

        $archivedProcesses =
            (new ProcessStatusArchived())->readListByScopesAndDates([$scope->getId()], $dates);

        $message = Response\Message::create($request);
        $message->data = $queueList->toProcessList()->withResolveLevel($resolveReferences);
        $message->data->addData($archivedProcesses);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
