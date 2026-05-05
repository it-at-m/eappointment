<?php

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Role;
use BO\Zmsentities\Role as RoleEntity;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Mellon\Validator;

class RoleAdd extends BaseController
{
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        (new Helper\User($request, 1))->checkRights('useraccount');

        $input = Validator::input()->isJson()->assertValid()->getValue();
        unset($input['id'], $input['assignedUserCount']);

        $entity = new RoleEntity($input);
        $entity->testValid();

        $role = (new Role())->addRole($entity);
        $message = Response\Message::create($request);
        $message->data = $role;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
