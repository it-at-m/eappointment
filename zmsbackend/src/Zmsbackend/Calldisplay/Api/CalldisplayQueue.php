<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Calldisplay\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Calldisplay\Helper\CalldisplayCollections;

/**
 * @SuppressWarnings(Coupling)
 * @return \Psr\Http\Message\ResponseInterface
 */
class CalldisplayQueue extends \BO\Zmsbackend\Api\BaseController
{
    /** @var array<int|string, \BO\Zmsentities\Scope> */
    protected array $scopeCache = [];

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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $statusList = Validator::param('statusList')->isArray()->setDefault([])->getValue();

        $input = Validator::input()->isJson()->assertValid()->getValue();
        $calldisplay = (new \BO\Zmsentities\Calldisplay($input))->withOutClusterDuplicates();
        $this->scopeCache = CalldisplayCollections::prepareForQueue($calldisplay, $resolveReferences);

        //read full list if no statusList exists
        $queueList = (count($statusList)) ?
            $this->readQueueListByStatus($calldisplay, $statusList, $resolveReferences) :
            $this->readFullQueueList($calldisplay, $resolveReferences);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $queueList->withoutDublicates();

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }

    protected function readCalculatedQueueListFromScope($scope, $resolveReferences)
    {
        $query = new \BO\Zmsbackend\Scope\Service\Scope();
        $scope = (isset($this->scopeCache[$scope->id])) ?
            $this->scopeCache[$scope->id] :
            $query->readWithWorkstationCount($scope->id, \App::$now, $resolveReferences);

        return $query
            ->readQueueListWithWaitingTime($scope, \App::$now, $resolveReferences);
    }

    // full queueList for calculation optimistic and estimated waiting Time and number of waiting clients
    protected function readFullQueueList(\BO\Zmsentities\Calldisplay $calldisplay, int $resolveReferences): \BO\Zmsentities\Collection\QueueList
    {
        $queueList = new \BO\Zmsentities\Collection\QueueList();
        foreach ($calldisplay->getFullScopeList() as $scope) {
            $queueList->addList($this->readCalculatedQueueListFromScope($scope, $resolveReferences));
        }
        return $queueList;
    }

    protected function readQueueListFromScopeAndStatus($scope, $status, $resolveReferences): \BO\Zmsentities\Collection\QueueList
    {
        $query = new \BO\Zmsbackend\Process\Service\Process();
        return $query
            ->readProcessListByScopeAndStatus($scope->getId(), $status, $resolveReferences)
            ->withinExactDate(\App::$now)
            ->toQueueList(\App::$now);
    }

    // short queueList only with status called and processing
    protected function readQueueListByStatus(\BO\Zmsentities\Calldisplay $calldisplay, $statusList, int $resolveReferences): \BO\Zmsentities\Collection\QueueList
    {
        $queueList = new \BO\Zmsentities\Collection\QueueList();
        foreach ($calldisplay->getFullScopeList() as $scope) {
            foreach ($statusList as $status) {
                $queueList
                    ->addList($this->readQueueListFromScopeAndStatus($scope, $status, $resolveReferences));
            }
        }
        return $queueList;
    }
}
