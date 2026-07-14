<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Scope\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Scope\Service\Scope as Query;

class ScopeListByCluster extends \BO\Zmsbackend\Api\BaseController
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
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readEntity($args['id']);
        if (! $cluster) {
            throw new \BO\Zmsbackend\Cluster\Exception\ClusterNotFound();
        }

        $scopeList = (new Query())->readByClusterId($cluster->id, $resolveReferences);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $scopeList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
