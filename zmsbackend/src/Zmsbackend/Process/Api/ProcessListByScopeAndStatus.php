<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Process\Service\Process;
use BO\Zmsbackend\Scope\Service\Scope;

class ProcessListByScopeAndStatus extends \BO\Zmsbackend\Api\BaseController
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
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($args['id'], $resolveReferences);
        if (! $scope) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }

        (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions(
            new \BO\Zmsentities\Useraccount\EntityAccess($scope)
        );

        $query = new \BO\Zmsbackend\Process\Service\Process();
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $query->readProcessListByScopeAndStatus($scope->id, $args['status'], $resolveReferences);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
