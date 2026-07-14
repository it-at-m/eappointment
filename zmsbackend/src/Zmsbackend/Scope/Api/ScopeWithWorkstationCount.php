<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Scope\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Scope\Service\Scope;

class ScopeWithWorkstationCount extends \BO\Zmsbackend\Api\BaseController
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
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readWithWorkstationCount($args['id'], \App::$now, $resolveReferences);
        if (! $scope) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }
        (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions(
            'appointment',
            new \BO\Zmsentities\Useraccount\EntityAccess($scope)
        );

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $scope;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
