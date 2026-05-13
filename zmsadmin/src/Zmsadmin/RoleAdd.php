<?php

namespace BO\Zmsadmin;

use BO\Zmsentities\Exception\UserAccountMissingRights;
use BO\Zmsentities\Role as RoleEntity;

class RoleAdd extends BaseController
{
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        if (!$workstation->getUseraccount()->hasPermissions(['useraccount'])) {
            throw new UserAccountMissingRights();
        }

        $validator = $request->getAttribute('validator');
        $confirmSuccess = $validator->getParameter('success')->isString()->getValue();
        $permissionList = \App::$http->readGetResult('/permissions/', [])->getCollection();

        $submitted = null;
        $result = null;
        if ($request->getMethod() === 'POST') {
            $input = $request->getParsedBody();
            $submitted = is_array($input) ? $input : [];
            $result = $this->writeNewRole($submitted);
            if ($result instanceof RoleEntity) {
                return \BO\Slim\Render::redirect(
                    'roleEdit',
                    ['id' => $result->id],
                    ['success' => 'role_added']
                );
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/roleForm.twig',
            [
                'title' => 'Rolle hinzufügen',
                'menuActive' => 'roles',
                'workstation' => $workstation,
                'permissionList' => $permissionList,
                'role' => $submitted,
                'formAction' => 'add',
                'success' => $confirmSuccess,
                'exception' => $result,
            ]
        );
    }

    protected function writeNewRole(array $input): RoleEntity|array|null
    {
        $data = $input;
        unset($data['id'], $data['assignedUserCount']);
        $permissions = $data['permissions'] ?? [];
        $data['permissions'] = is_array($permissions)
            ? array_values(array_unique($permissions))
            : [];
        $entity = (new RoleEntity($data))->withCleanedUpFormData();

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

        return $this->handleEntityWrite(function () use ($entity) {
            return \App::$http->readPostResult('/roles/', $entity)->getEntity();
        });
    }
}
