<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsapi\Helper\User as UserHelper;
use BO\Zmsdb\Role as RoleRepository;

class RolesUpdate extends BaseController
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
        // Only superusers may edit roles & permissions.
        UserHelper::$request = $request;
        $workstation = UserHelper::readWorkstation(1);
        if (!$workstation->getUseraccount()->isSuperUser()) {
            throw new \BO\Zmsentities\Exception\UserAccountMissingPermissions('Missing superuser permission');
        }

        $rawBody = (string) $request->getBody();
        $decoded = json_decode($rawBody, true);
        if (is_array($decoded)) {
            $data = $decoded;
        } else {
            $data = $request->getParsedBody() ?? [];
            if (!is_array($data)) {
                $data = [];
            }
        }

        $rolesInput = isset($data['roles']) && is_array($data['roles']) ? $data['roles'] : [];
        $newRoleInput = isset($data['newRole']) && is_array($data['newRole']) ? $data['newRole'] : [];
        $deleteIds = isset($data['delete']) && is_array($data['delete']) ? $data['delete'] : [];

        $repository = new RoleRepository();
        $repository->updateRoleAssignments($rolesInput, $deleteIds, $newRoleInput);

        // Return updated matrix for convenience (same shape as RolesGet)
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
