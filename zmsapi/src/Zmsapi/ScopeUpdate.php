<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope;

class ScopeUpdate extends BaseController
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
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $scope = (new Scope)->readEntity($args['id'], 1);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $scope->addData($input)->testValid('de_DE', 1);
        (new Helper\User($request, 2))->checkRights(
            'scope',
            new \BO\Zmsentities\Useraccount\EntityAccess($scope)
        );

        $message = Response\Message::create($request);
        $message->data = (new Scope)->updateEntity($scope->id, $scope);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
