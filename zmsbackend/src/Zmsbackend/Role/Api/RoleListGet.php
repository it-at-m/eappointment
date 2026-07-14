<?php

namespace BO\Zmsbackend\Role\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Role\Service\Role;

class RoleListGet extends \BO\Zmsbackend\Api\BaseController
{
    public function readResponse($request, $response, array $args)
    {
        (new \BO\Zmsbackend\Helper\User($request, 1))->checkPermissions('useraccount');

        $roleList = (new \BO\Zmsbackend\Role\Service\Role())->readAllRoles();
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $roleList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
