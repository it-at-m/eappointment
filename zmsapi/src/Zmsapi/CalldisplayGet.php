<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Calldisplay as Query;
use BO\Zmsentities\Calldisplay as Entity;

/**
 * @SuppressWarnings(Coupling)
 * @return String
 */
class CalldisplayGet extends BaseController
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
        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new Entity($input);

        $this->testScopeAndCluster($entity);
        $message = Response\Message::create($request);
        $message->data = $query->readResolvedEntity($entity, \App::getNow(), $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testScopeAndCluster($calldisplay)
    {
        if (! $calldisplay->hasScopeList() && ! $calldisplay->hasClusterList()) {
            throw new Exception\Calldisplay\ScopeAndClusterNotFound();
        }
        foreach ($calldisplay->getClusterList() as $cluster) {
            $cluster = (new \BO\Zmsdb\Cluster())->readEntity($cluster->id);
            if (! $cluster) {
                throw new Exception\Cluster\ClusterNotFound();
            }
        }
        foreach ($calldisplay->getScopeList() as $scope) {
            $scope = (new \BO\Zmsdb\Scope())->readEntity($scope->id);
            if (! $scope) {
                throw new Exception\Scope\ScopeNotFound();
            }
        }
    }
}
