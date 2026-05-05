<?php

namespace BO\Zmsapi;

use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsdb\Role;
use BO\Zmsentities\Role as RoleEntity;

class RoleUpdate extends BaseController
{
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new Helper\User($request, 1))->checkRights('useraccount');

        $roleId = (int) $args['id'];
        $input = Validator::input()->isJson()->assertValid()->getValue();
        unset($input['id'], $input['assignedUserCount']);

        $entity = new RoleEntity($input);
        $entity->testValid();

        $updated = (new Role())->updateRole($roleId, $entity);
        if (!$updated || !$updated->hasId()) {
            throw new Exception\Role\RoleNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $updated;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
