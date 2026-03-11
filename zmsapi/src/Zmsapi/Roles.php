<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsapi\Helper\User as UserHelper;

class Roles extends BaseController
{
    // Kept only for backwards compatibility with older clients that still call /roles/
    // New code should use RolesGet (GET /roles/) and RolesUpdate (POST /roles/).

    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        if ($request->getMethod() === 'POST') {
            $delegate = new RolesUpdate();
        } else {
            $delegate = new RolesGet();
        }

        return $delegate->readResponse($request, $response, $args);
    }
}

