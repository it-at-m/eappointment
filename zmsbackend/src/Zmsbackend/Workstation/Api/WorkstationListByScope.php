<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Workstation\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Workstation\Service\Workstation;
use BO\Zmsentities\Helper\DateTime;

class WorkstationListByScope extends \BO\Zmsbackend\Api\BaseController
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
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($args['id'], 0);
        if (! $scope) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }
        $workstationList = (new \BO\Zmsbackend\Workstation\Service\Workstation())->readLoggedInListByScope($scope->id, \App::$now, $resolveReferences);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $workstationList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
