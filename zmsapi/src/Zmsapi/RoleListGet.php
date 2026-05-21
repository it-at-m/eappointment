<?php

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Role;

class RoleListGet extends BaseController
{
    public function readResponse($request, $response, array $args)
    {
        (new Helper\User($request, 1))->checkPermissions('useraccount');

        $roleList = (new Role())->readAllRoles();
        $message = Response\Message::create($request);
        $message->data = $roleList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
