<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Ticketprinter\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Request\Service\Request;
use BO\Zmsbackend\Ticketprinter\Service\Ticketprinter as Query;
use BO\Zmsbackend\Scope\Service\Scope;
use BO\Zmsbackend\Process\Service\ProcessStatusQueued;

class TicketprinterWaitingnumberByScope extends \BO\Zmsbackend\Api\BaseController
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
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $validator = $request->getAttribute('validator');
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($args['id'], 0);
        $requestId = $validator->getParameter('requestId')->isNumber()->getValue();
        if (! $scope) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }

        $process = \BO\Zmsbackend\Process\Service\ProcessStatusQueued::init()->writeNewFromTicketprinter($scope, \App::$now, [$requestId]);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
