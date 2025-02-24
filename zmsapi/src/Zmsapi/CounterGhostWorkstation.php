<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Scope;
use BO\Zmsentities\Scope as Entity;

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
        $workstation = (new Helper\User($request))->checkRights('basic');
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new Entity($input);
        $entity->testValid();
        $scope = (new Scope())->readEntity($entity->id, 0);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        if ($scope->id != $workstation->getScope()->id) {
            throw new Exception\Scope\ScopeNoAccess();
        }
        $message = Response\Message::create($request);
        $message->data = (new Scope())->updateGhostWorkstationCount($entity, \App::$now);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
