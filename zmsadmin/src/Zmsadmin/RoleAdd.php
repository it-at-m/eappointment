<?php

namespace BO\Zmsadmin;

use BO\Zmsentities\Exception\SchemaValidation;
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
        if (!$workstation->getUseraccount()->hasRights(['useraccount'])) {
            throw new UserAccountMissingRights();
        }

        $confirmSuccess = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $permissionList = \App::$http->readGetResult('/permissions/', [])->getCollection();

        $submitted = null;
        if ($request->getMethod() === 'POST') {
            $input = $this->buildRoleInputFromRequest($request);
            $submitted = $input;
            $result = $this->writeNewRole($input);
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
                'exception' => isset($result) ? $result : null,
            ]
        );
    }

    protected function buildRoleInputFromRequest(\Psr\Http\Message\RequestInterface $request): array
    {
        $body = $request->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }
        $name = isset($body['name']) ? trim((string) $body['name']) : '';
        $description = isset($body['description']) ? trim((string) $body['description']) : '';
        $perms = $body['permissions'] ?? [];
        if (!is_array($perms)) {
            $perms = $perms !== null && $perms !== '' ? [$perms] : [];
        }
        $perms = array_values(array_filter(array_map('strval', $perms)));

        return [
            'name' => $name,
            'description' => $description === '' ? null : $description,
            'permissions' => $perms,
        ];
    }

    protected function writeNewRole(array $input): RoleEntity|array|null
    {
        $entity = new RoleEntity($input);
        try {
            $entity->testValid();
        } catch (SchemaValidation $e) {
            return [
                'template' => 'exception/bo/zmsentities/exception/schemavalidation.twig',
                'include' => true,
                'data' => $this->transformValidationErrors($e->data),
            ];
        }

        return $this->handleEntityWrite(function () use ($entity) {
            return \App::$http->readPostResult('/roles/', $entity)->getEntity();
        });
    }
}
