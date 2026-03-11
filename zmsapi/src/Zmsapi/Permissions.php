<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsapi\Helper\User as UserHelper;
use BO\Zmsdb\Permission as PermissionRepository;

class Permissions extends BaseController
{
    // Kept only for backwards compatibility with older clients that still call /permissions/.
    // New code should use PermissionsGet (GET /permissions/).

    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $delegate = new PermissionsGet();
        return $delegate->readResponse($request, $response, $args);
    }
}

