<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 */

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsentities\Exception\UserAccountMissingRights;

class RoleDelete extends BaseController
{
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        if (!$workstation->getUseraccount()->hasRights(['useraccount'])) {
            throw new UserAccountMissingRights();
        }

        $roleId = (int) Validator::value($args['id'] ?? null)->isNumber()->getValue();
        \App::$http->readDeleteResult('/roles/' . $roleId . '/', [])->getEntity();

        return \BO\Slim\Render::redirect(
            'roles',
            [],
            [
                'success' => 'role_deleted',
            ]
        );
    }
}
