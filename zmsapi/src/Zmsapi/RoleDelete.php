<?php

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Role;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RoleDelete extends BaseController
{
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        (new Helper\User($request, 1))->checkPermissions('superuser');

        $roleId = (int) $args['id'];
        $roleModel = new Role();
        $roleEntity = $roleModel->readRoleById($roleId);

        if (!$roleEntity || ! $roleEntity->hasId()) {
            throw new Exception\Role\RoleDoesNotExist();
        }

        try {
            $roleModel->deleteRole($roleEntity->id);
        } catch (\BO\Zmsdb\Exception\Role\AssignedUserListNotEmpty $e) {
            throw new Exception\Role\RoleHasAssignedUsers('', 0, $e);
        }

        $message = Response\Message::create($request);
        $message->data = $roleEntity;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
