<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Workstation\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Useraccount\Service\Profile as ProfileService;

class WorkstationProfileGet extends \BO\Zmsbackend\Api\BaseController
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
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = (new \BO\Zmsbackend\Helper\User($request, 1))
            ->checkPermissions();

        $profile = (new ProfileService())->readEntity(
            $workstation->getUseraccount()
        );

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $profile;

        $response = Render::withLastModified($response, time(), '0');

        return Render::withJson(
            $response,
            $message->setUpdatedMetaData(),
            $message->getStatuscode()
        );
    }
}
