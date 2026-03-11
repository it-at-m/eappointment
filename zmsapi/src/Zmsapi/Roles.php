<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Role as RoleRepository;
use BO\Zmsapi\Helper\User as UserHelper;

class Roles extends BaseController
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
        // Use the resolved workstation and its permissions-based isSuperUser().
        UserHelper::$request = $request;
        $workstation = UserHelper::readWorkstation(1);
        if (!$workstation->getUseraccount()->isSuperUser()) {
            throw new \BO\Zmsentities\Exception\UserAccountMissingPermissions('Missing superuser permission');
        }

        if ($request->getMethod() === 'POST') {
            $this->handlePost($request);
        }

        $roles = $this->readRolePermissionMatrix();

        $message = Response\Message::create($request);
        $message->data = $roles;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, $message->getStatuscode());
    }

    /**
     * Read all roles and expand them into permission name lists using zmsdb.
     *
     * @return array[]
     */
    protected function readRolePermissionMatrix(): array
    {
        $repository = new RoleRepository();
        $roles = $repository->readRolePermissionMatrix();

        $schemaUrl = 'https://schema.berlin.de/queuemanagement/role.json';
        foreach ($roles as &$role) {
            $role['$schema'] = $schemaUrl;
        }
        unset($role);

        return $roles;
    }

    /**
     * Handle create/update/delete actions for roles and their permissions.
     *
     * Expects the same payload structure that zmsadmin sends:
     *  - roles[<id>][name], roles[<id>][description], roles[<id>][permissions][]
     *  - newRole[name], newRole[description], newRole[permissions][]
     *  - delete[] (array of role IDs to delete)
     */
    protected function handlePost(\Psr\Http\Message\RequestInterface $request): void
    {
        // Support both direct form POSTs and proxied JSON from zmsadmin.
        // Prefer explicit JSON body when present, otherwise fall back to parsed body.
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
    }
}

