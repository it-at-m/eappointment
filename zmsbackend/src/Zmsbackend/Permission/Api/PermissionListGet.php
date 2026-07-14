<?php

namespace BO\Zmsbackend\Permission\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Permission\Service\Permission as PermissionRepository;

class PermissionListGet extends \BO\Zmsbackend\Api\BaseController
{
    public function readResponse($request, $response, array $args)
    {
        (new \BO\Zmsbackend\Helper\User($request, 1))->checkPermissions('superuser');

        $list = (new PermissionRepository())->readAllPermissions();
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $list;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
