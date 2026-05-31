<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Process;
use BO\Zmsdb\Scope;

class ProcessListByScopeAndStatus extends BaseController
{
    /**
     * @SuppressWarnings (Param)
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scope = (new Scope())->readEntity($args['id'], $resolveReferences);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }

        (new Helper\User($request, 2))->checkPermissions(
            new \BO\Zmsentities\Useraccount\EntityAccess($scope)
        );

        $query = new Process();
        $message = Response\Message::create($request);
        $message->data = $query->readProcessListByScopeAndStatus($scope->id, $args['status'], $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
