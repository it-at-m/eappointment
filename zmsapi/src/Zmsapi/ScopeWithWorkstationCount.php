<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Scope;

class ScopeWithWorkstationCount extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scope = (new Scope())->readWithWorkstationCount($args['id'], \App::$now, $resolveReferences);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        (new Helper\User($request, 2))->checkRights(
            'basic',
            new \BO\Zmsentities\Useraccount\EntityAccess($scope)
        );

        $message = Response\Message::create($request);
        $message->data = $scope;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
