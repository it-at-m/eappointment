<?php

namespace BO\Zmsadmin;

use BO\Zmsadmin\Helper\RoleInputHelper;
use BO\Mellon\Validator;
use BO\Zmsentities\Exception\UserAccountMissingRights;
use BO\Zmsentities\Role as RoleEntity;

class RoleEdit extends BaseController
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
        $validator = $request->getAttribute('validator');
        $confirmSuccess = $validator->getParameter('success')->isString()->getValue();
        $permissionList = \App::$http->readGetResult('/permissions/', [])->getCollection();

        $role = \App::$http->readGetResult('/roles/' . $roleId . '/', [])->getEntity();
        if (!$role->hasId()) {
            return \BO\Slim\Render::redirect('roles', [], []);
        }

        $submitted = null;
        $result = null;
        if ($request->getMethod() === 'POST') {
            $input = $request->getParsedBody();
            $submitted = is_array($input) ? $input : [];
            $result = $this->writeUpdatedRole($roleId, $role->name, $submitted);
            if ($result instanceof RoleEntity) {
                return \BO\Slim\Render::redirect(
                    'roleEdit',
                    ['id' => $roleId],
                    ['success' => 'role_updated']
                );
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/roleForm.twig',
            [
                'title' => 'Rolle bearbeiten',
                'menuActive' => 'roles',
                'workstation' => $workstation,
                'permissionList' => $permissionList,
                'role' => $submitted !== null ? $submitted : $role,
                'formAction' => 'edit',
                'roleId' => $roleId,
                'success' => $confirmSuccess,
                'exception' => $result,
            ]
        );
    }

    protected function writeUpdatedRole(int $roleId, string $currentRoleName, array $input): RoleEntity|array|null
    {
        $data = $input;
        unset($data['id'], $data['assignedUserCount']);
        $permissions = $data['permissions'] ?? [];
        $data['permissions'] = is_array($permissions)
            ? array_values(array_unique($permissions))
            : [];
        $entity = (new RoleEntity($data))->withCleanedUpFormData();

        if ($entity->name !== $currentRoleName) {
            $roles = \App::$http->readGetResult('/roles/', [])->getCollection();
            foreach ($roles as $existing) {
                if ((string) $existing->name === $entity->name) {
                    return [
                        'template' => 'exception/bo/zmsentities/exception/schemavalidation.twig',
                        'include' => true,
                        'data' => [
                            'name' => [
                                'messages' => ['Eine Rolle mit diesem Namen existiert bereits.'],
                            ],
                        ],
                    ];
                }
            }
        }

        return $this->handleEntityWrite(function () use ($roleId, $entity) {
            return \App::$http->readPostResult('/roles/' . $roleId . '/', $entity)->getEntity();
        });
    }
}
