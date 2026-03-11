<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsapi\Helper\User as UserHelper;
use BO\Zmsdb\Role as RoleRepository;

class RolesGet extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return string
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        // Only superusers may inspect or edit roles & permissions.
        UserHelper::$request = $request;
        $workstation = UserHelper::readWorkstation(1);
        if (!$workstation->getUseraccount()->isSuperUser()) {
            throw new \BO\Zmsentities\Exception\UserAccountMissingPermissions('Missing superuser permission');
        }

        $repository = new RoleRepository();
        $roles = $repository->readRolePermissionMatrix();

        $schemaUrl = 'https://schema.berlin.de/queuemanagement/role.json';
        foreach ($roles as &$role) {
            $role['$schema'] = $schemaUrl;
        }
        unset($role);

        $message = Response\Message::create($request);
        $message->data = $roles;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, $message->getStatuscode());
    }
}
