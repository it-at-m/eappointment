<?php

namespace BO\Zmsbackend\Role\Api;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsbackend\Role\Service\Role;
use BO\Zmsentities\Role as RoleEntity;

class RoleUpdate extends \BO\Zmsbackend\Api\BaseController
{
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new \BO\Zmsbackend\Helper\User($request, 1))->checkPermissions('superuser');

        $roleId = (int) $args['id'];
        $input = Validator::input()->isJson()->assertValid()->getValue();
        unset($input['id'], $input['assignedUserCount']);

        $entity = new RoleEntity($input);
        $entity->testValid();

        $updated = (new \BO\Zmsbackend\Role\Service\Role())->updateRole($roleId, $entity);
        if (!$updated || !$updated->hasId()) {
            throw new \BO\Zmsbackend\Role\Exception\RoleDoesNotExist();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $updated;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
