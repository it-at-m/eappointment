<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Scope\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Scope\Service\Scope;

class ScopeUpdate extends \BO\Zmsbackend\Api\BaseController
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
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $existingScope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($args['id'], 1);
        if (! $existingScope) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }
        $scope = clone $existingScope;
        $scope->addData($input);
        $scope->id = $existingScope->id;
        $scope->testValid('de_DE', 1);
        $user = new \BO\Zmsbackend\Helper\User($request, 2);

        $user->checkAnyPermission(
            'restrictedscope',
            'scope'
        );

        $user->checkRights(
            new \BO\Zmsentities\Useraccount\EntityAccess($existingScope)
        );

        if (! \BO\Zmsbackend\Helper\User::readWorkstation()->getUseraccount()->hasPermissions(['scope'])) {
            $scope = $scope->withProviderSourceFrom($existingScope);
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new \BO\Zmsbackend\Scope\Service\Scope())->updateEntity($scope->id, $scope);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
