<?php

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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (!$workstation->getUseraccount()->hasPermissions(['superuser'])) {
            throw new UserAccountMissingRights();
        }

        $roleId = (int) Validator::value($args['id'] ?? null)->isNumber()->getValue();
        \App::$http->readDeleteResult('/roles/' . $roleId . '/', [])->getEntity();

        return $response->withStatus(204);
    }
}
