<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope;
use \BO\Zmsentities\Scope as Entity;

class CounterGhostWorkstation extends BaseController
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
        $workstation = Helper\User::checkRights('basic');
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $scope = new Entity($input);
        $scope->testValid();
        $scope = (new Scope)->readEntity($scope->id, 0);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        if ($scope->id != $workstation->getScope()->id) {
            throw new Exception\Scope\ScopeNoAccess();
        }
        $message = Response\Message::create($request);
        $message->data = (new Scope())->updateGhostWorkstationCount($scope, \App::$now);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
