<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process;

class Pickup extends BaseController
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
        $workstation = (new Helper\User($request))->checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();

        $scope = (new \BO\Zmsdb\Scope)->readEntity($workstation->scope['id'], 0);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }

        $cluster = (new \BO\Zmsdb\Cluster())->readByScopeId($workstation->scope['id'], $resolveReferences);
        $clusterEnabled = (1 == $workstation->queue['clusterEnabled']);
        if ($clusterEnabled && ! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }
        $scopeList = $workstation->getScopeList($cluster);

        $processList = new \BO\Zmsentities\Collection\ProcessList();
        foreach ($scopeList as $scope) {
            $list = (new Process)->readProcessListByScopeAndStatus($scope['id'], 'pending', $resolveReferences);
            if ($list->count()) {
                $processList->addList($list);
            }
        }
        $message = Response\Message::create($request);
        $message->data = ($clusterEnabled) ? $processList : $processList->withScopeId($scope->getId());

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
