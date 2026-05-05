<?php

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Role;

class RoleGet extends BaseController
{
    public function readResponse($request, $response, array $args)
    {
        (new Helper\User($request, 1))->checkRights('useraccount');

        $roleId = (int) $args['id'];
        $role = (new Role())->readRoleById($roleId);

        if (! $role->hasId()) {
            throw new Exception\Role\RoleNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $role;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
