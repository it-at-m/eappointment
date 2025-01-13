<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Scope;

class ScopeEmergencyRespond extends BaseController
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
        $workstation = (new Helper\User($request, 1))->checkRights();
        if (! $workstation->getScopeList()->hasEntity($args['id'])) {
            throw new Exception\Scope\ScopeNoAccess();
        }

        $workstation->scope->status['emergency']['acceptedByWorkstation'] = $workstation->getName();

        $message = Response\Message::create($request);
        $message->data = (new Scope())->updateEmergency($args['id'], $workstation->scope);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
