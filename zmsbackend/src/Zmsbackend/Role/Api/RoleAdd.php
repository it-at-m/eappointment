<?php

namespace BO\Zmsbackend\Role\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Role\Service\Role;
use BO\Zmsentities\Role as RoleEntity;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Mellon\Validator;

class RoleAdd extends \BO\Zmsbackend\Api\BaseController
{
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        (new \BO\Zmsbackend\Helper\User($request, 1))->checkPermissions('superuser');

        $input = Validator::input()->isJson()->assertValid()->getValue();
        unset($input['id'], $input['assignedUserCount']);

        $entity = new RoleEntity($input);
        $entity->testValid();

        $role = (new \BO\Zmsbackend\Role\Service\Role())->addRole($entity);
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $role;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
