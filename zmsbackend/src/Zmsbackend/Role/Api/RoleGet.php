<?php

namespace BO\Zmsbackend\Role\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Role\Service\Role;

class RoleGet extends \BO\Zmsbackend\Api\BaseController
{
    public function readResponse($request, $response, array $args)
    {
        (new \BO\Zmsbackend\Helper\User($request, 1))->checkPermissions('superuser');

        $roleId = (int) $args['id'];
        $role = (new \BO\Zmsbackend\Role\Service\Role())->readRoleById($roleId);

        if (!$role || ! $role->hasId()) {
            throw new \BO\Zmsbackend\Role\Exception\RoleDoesNotExist();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $role;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
