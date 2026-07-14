<?php

/**
 * @package Zmsbackend
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Request\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Request\Service\Request;

class RequestListByCluster extends \BO\Zmsbackend\Api\BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readEntity($args['id'], $resolveReferences ? $resolveReferences : 2);
        if (! $cluster) {
            throw new \BO\Zmsbackend\Cluster\Exception\ClusterNotFound();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new \BO\Zmsbackend\Request\Service\Request())->readListByCluster($cluster, $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
