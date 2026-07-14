<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Calldisplay\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Calldisplay\Service\Calldisplay as Query;
use BO\Zmsentities\Calldisplay as Entity;

/**
 * @SuppressWarnings(Coupling)
 * @return \Psr\Http\Message\ResponseInterface
 */
class CalldisplayGet extends \BO\Zmsbackend\Api\BaseController
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
        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new Entity($input);

        $this->testScopeAndCluster($entity);
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $query->readResolvedEntity($entity, \App::getNow(), $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testScopeAndCluster($calldisplay)
    {
        if (! $calldisplay->hasScopeList() && ! $calldisplay->hasClusterList()) {
            throw new \BO\Zmsbackend\Calldisplay\Exception\ScopeAndClusterNotFound();
        }
        foreach ($calldisplay->getClusterList() as $cluster) {
            $cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readEntity($cluster->id);
            if (! $cluster) {
                throw new \BO\Zmsbackend\Cluster\Exception\ClusterNotFound();
            }
        }
        foreach ($calldisplay->getScopeList() as $scope) {
            $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($scope->id);
            if (! $scope) {
                throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
            }
        }
    }
}
