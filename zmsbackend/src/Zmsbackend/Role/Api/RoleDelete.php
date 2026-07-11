<?php

namespace BO\Zmsbackend\Role\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Role\Service\Role;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RoleDelete extends \BO\Zmsbackend\Api\BaseController
{
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        (new \BO\Zmsbackend\Helper\User($request, 1))->checkPermissions('superuser');

        $roleId = (int) $args['id'];
        $roleModel = new \BO\Zmsbackend\Role\Service\Role();
        $roleEntity = $roleModel->readRoleById($roleId);

        if (!$roleEntity || ! $roleEntity->hasId()) {
            throw new \BO\Zmsbackend\Role\Exception\RoleDoesNotExist();
        }

        try {
            $roleModel->deleteRole($roleEntity->id);
        } catch (\BO\Zmsbackend\Role\Exception\AssignedUserListNotEmpty $e) {
            throw new \BO\Zmsbackend\Role\Exception\RoleHasAssignedUsers('', 0, $e);
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $roleEntity;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
