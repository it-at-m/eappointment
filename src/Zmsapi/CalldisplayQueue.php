<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;

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
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $calldisplay = (new \BO\Zmsentities\Calldisplay($input))->withOutClusterDuplicates();
        $this->testScopeAndCluster($calldisplay, $resolveReferences);

        $queueList = new \BO\Zmsentities\Collection\QueueList();
        foreach ($calldisplay->getFullScopeList() as $scope) {
            $queueList->addList($this->readCalculatedQueueListFromScope($scope, $resolveReferences));
        }

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
            $cluster = (new \BO\Zmsdb\Cluster)->readEntity($cluster->id);
            if (! $cluster) {
                throw new Exception\Cluster\ClusterNotFound();
            }
        }
        foreach ($calldisplay->getScopeList() as $scope) {
            $scope = (new \BO\Zmsdb\Scope)->readWithWorkstationCount($scope->id, \App::$now, $resolveReferences);
            $this->scopeCache[$scope->id] = $scope;
            if (! $scope->id) {
                throw new Exception\Scope\ScopeNotFound();
            }
        }
    }

    protected function readCalculatedQueueListFromScope($scope, $resolveReferences)
    {
        if (isset($this->scopeCache[$scope->id])) {
            $scope = $this->scopeCache[$scope->id];
        } else {
            $scope = (new \BO\Zmsdb\Scope)->readWithWorkstationCount($scope->id, \App::$now, $resolveReferences);
        }
        // TODO try to fetch only called processes
        return (new \BO\Zmsdb\Scope)
            ->readQueueListWithWaitingTime($scope, \App::$now)
            ->withPickupDestination($scope);
    }
}
