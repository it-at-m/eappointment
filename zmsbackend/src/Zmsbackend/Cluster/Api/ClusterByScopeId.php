<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Cluster\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Cluster\Service\Cluster as Query;

class ClusterByScopeId extends \BO\Zmsbackend\Api\BaseController
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
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();

        if ((new \BO\Zmsbackend\Helper\User($request))->hasLogin() || $resolveReferences > 0) {
            $resolveReferences = ($resolveReferences > 0 ) ? $resolveReferences : 1;
            (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();
        } else {
            $message->meta->reducedData = true;
        }

        $cluster = (new Query())->readByScopeId($args['id'], $resolveReferences);
        $message->data = ($cluster) ? $cluster : array();

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
