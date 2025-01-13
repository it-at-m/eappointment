<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;

/**
 * @SuppressWarnings(Coupling)
 * @return String
 */
class CalldisplayQueue extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $statusList = Validator::param('statusList')->isArray()->setDefault([])->getValue();

        $input = Validator::input()->isJson()->assertValid()->getValue();
        $calldisplay = (new \BO\Zmsentities\Calldisplay($input))->withOutClusterDuplicates();
        $this->testScopeAndCluster($calldisplay, $resolveReferences);

        //read full list if no statusList exists
        $queueList = (count($statusList)) ?
            $this->readQueueListByStatus($calldisplay, $statusList, $resolveReferences) :
            $this->readFullQueueList($calldisplay, $resolveReferences);



        $message = Response\Message::create($request);
        $message->data = $queueList->withoutDublicates();

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }

    protected $scopeCache = [];

    protected function testScopeAndCluster($calldisplay, $resolveReferences)
    {
        if (! $calldisplay->hasScopeList() && ! $calldisplay->hasClusterList()) {
            throw new Exception\Calldisplay\ScopeAndClusterNotFound();
        }
        foreach ($calldisplay->getClusterList() as $cluster) {
            $cluster = (new \BO\Zmsdb\Cluster())->readEntity($cluster->getId());
            if (! $cluster) {
                throw new Exception\Cluster\ClusterNotFound();
            }
        }
        foreach ($calldisplay->getScopeList() as $scope) {
            $scope = (new \BO\Zmsdb\Scope())->readWithWorkstationCount($scope->getId(), \App::$now, $resolveReferences);
            if (! $scope) {
                throw new Exception\Scope\ScopeNotFound();
            }
            $this->scopeCache[$scope->getId()] = $scope;
        }
    }

    protected function readCalculatedQueueListFromScope($scope, $resolveReferences)
    {
        $query = new \BO\Zmsdb\Scope();
        $scope = (isset($this->scopeCache[$scope->id])) ?
            $this->scopeCache[$scope->id] :
            $query->readWithWorkstationCount($scope->id, \App::$now, $resolveReferences);

        return $query
            ->readQueueListWithWaitingTime($scope, \App::$now, $resolveReferences)
            ->withPickupDestination($scope);
    }

    // full queueList for calculation optimistic and estimated waiting Time and number of waiting clients
    protected function readFullQueueList($calldisplay, $resolveReferences)
    {
        $queueList = new \BO\Zmsentities\Collection\QueueList();
        foreach ($calldisplay->getFullScopeList() as $scope) {
            $queueList->addList($this->readCalculatedQueueListFromScope($scope, $resolveReferences));
        }
        return $queueList;
    }

    protected function readQueueListFromScopeAndStatus($scope, $status, $resolveReferences)
    {
        $query = new \BO\Zmsdb\Process();
        return $query
            ->readProcessListByScopeAndStatus($scope->getId(), $status, $resolveReferences)
            ->withinExactDate(\App::$now)
            ->toQueueList(\App::$now)
            ->withPickupDestination($scope);
    }

    // short queueList only with status called, pickup and processing
    protected function readQueueListByStatus($calldisplay, $statusList, $resolveReferences)
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
