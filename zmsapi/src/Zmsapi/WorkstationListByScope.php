<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Workstation;
use BO\Zmsentities\Helper\DateTime;

class WorkstationListByScope extends BaseController
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
        $scope = (new \BO\Zmsdb\Scope())->readEntity($args['id'], 0);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $workstationList = (new Workstation())->readLoggedInListByScope($scope->id, \App::$now, $resolveReferences);

        $message = Response\Message::create($request);
        $message->data = $workstationList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
