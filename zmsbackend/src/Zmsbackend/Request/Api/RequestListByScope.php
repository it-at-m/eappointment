<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Request\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Request\Service\Request;

class RequestListByScope extends \BO\Zmsbackend\Api\BaseController
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
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($args['id'], $resolveReferences ? $resolveReferences : 1);
        if (! $scope) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }
        $requestList = (new \BO\Zmsbackend\Request\Service\Request())
            ->readListByProvider($scope->provider['source'], $scope->getProviderId(), $resolveReferences);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $requestList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
