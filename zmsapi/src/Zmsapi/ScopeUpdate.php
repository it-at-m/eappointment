<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Scope;

class ScopeUpdate extends BaseController
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
        $existingScope = (new Scope())->readEntity($args['id'], 1);
        if (! $existingScope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $scope = clone $existingScope;
        $scope->addData($input);
        $scope->id = $existingScope->id;
        $scope->testValid('de_DE', 1);
        $user = new Helper\User($request, 2);

        $user->checkAnyPermission(
            'restrictedscope',
            'scope'
        );

        $user->checkPermissions(
            new \BO\Zmsentities\Useraccount\EntityAccess($existingScope)
        );

        if (! Helper\User::readWorkstation()->getUseraccount()->hasPermissions(['scope'])) {
            $scope = $scope->withProviderSourceFrom($existingScope);
        }

        $message = Response\Message::create($request);
        $message->data = (new Scope())->updateEntity($scope->id, $scope);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
