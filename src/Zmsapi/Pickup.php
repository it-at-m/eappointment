<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\Cluster;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
  * Handle requests concerning services
  */
class Pickup extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $workstation = (new Helper\User($request))->checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scopeList = (new \BO\Zmsentities\Collection\ScopeList)->addEntity($workstation->scope);
        if (1 == $workstation->queue['clusterEnabled']) {
            $cluster = (new Cluster())->readByScopeId($workstation->scope['id'], $resolveReferences);
            if (! $cluster) {
                throw new Exception\Cluster\ClusterNotFound();
            }
            $scopeList = $scopeList = (new \BO\Zmsentities\Collection\ScopeList)->addList($cluster->scopes);
        }
        $query = new Query();
        $processList = new \BO\Zmsentities\Collection\ProcessList();
        foreach ($scopeList as $scope) {
            $list = $query->readProcessListByScopeAndStatus($scope['id'], 'pending', $resolveReferences);
            if ($list->count()) {
                $processList->addList($list);
            }
        }
        $message = Response\Message::create($request);
        $message->data = $processList;
        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
