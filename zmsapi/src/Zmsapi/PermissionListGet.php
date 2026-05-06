<?php

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Permission as PermissionRepository;

class PermissionListGet extends BaseController
{
    public function readResponse($request, $response, array $args)
    {
        (new Helper\User($request, 1))->checkRights('useraccount');

        $list = (new PermissionRepository())->readAllPermissions();
        $message = Response\Message::create($request);
        $message->data = $list;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
