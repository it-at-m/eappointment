<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Scope\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Scope\Service\Scope as Query;

class ScopeCalldisplayImageDataDelete extends \BO\Zmsbackend\Api\BaseController
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
        $scope = (new Query())->readEntity($args['id']);
        if (! $scope) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }(new \BO\Zmsbackend\Helper\User($request, 2))->checkRights(
            'scope',
            new \BO\Zmsentities\Useraccount\EntityAccess($scope)
        );

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $result = (new Query())->deleteImage($scope->id);
        $message->data = $result;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
