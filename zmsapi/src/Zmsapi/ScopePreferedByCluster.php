<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Cluster;
use BO\Zmsdb\Scope;

class ScopePreferedByCluster extends BaseController
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
        (new Helper\User($request))->checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $cluster = (new \BO\Zmsdb\Cluster())->readEntity($args['id']);
        if (! $cluster) {
            throw new Exception\Cluster\ClusterNotFound();
        }
        $scope = (new Cluster())->readScopeWithShortestWaitingTime($cluster->id, \App::$now);
        $scope = (new Scope())->readResolvedReferences($scope, $resolveReferences);

        $message = Response\Message::create($request);
        $message->data = $scope;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
