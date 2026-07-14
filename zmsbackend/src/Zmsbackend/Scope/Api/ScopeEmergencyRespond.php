<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Scope\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Scope\Service\Scope;

class ScopeEmergencyRespond extends \BO\Zmsbackend\Api\BaseController
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
        $workstation = (new \BO\Zmsbackend\Helper\User($request, 1))->checkPermissions();
        if (! $workstation->getScopeList()->hasEntity($args['id'])) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNoAccess();
        }

        $workstation->scope->status['emergency']['acceptedByWorkstation'] = $workstation->getName();

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new \BO\Zmsbackend\Scope\Service\Scope())->updateEmergency($args['id'], $workstation->scope);

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
