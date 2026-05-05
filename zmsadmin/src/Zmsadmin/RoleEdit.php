<?php

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsentities\Exception\SchemaValidation;
use BO\Zmsentities\Exception\UserAccountMissingRights;
use BO\Zmsentities\Role as RoleEntity;

class RoleEdit extends BaseController
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
        $confirmSuccess = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        $permissionList = \App::$http->readGetResult('/permissions/', [])->getCollection();

        $role = \App::$http->readGetResult('/roles/' . $roleId . '/', [])->getEntity();
        if (!$role->hasId()) {
            return \BO\Slim\Render::redirect('roles', [], []);
        }

        $submitted = null;
        if ($request->getMethod() === 'POST') {
            $input = $this->buildRoleInputFromRequest($request);
            $submitted = $input;
            $result = $this->writeUpdatedRole($roleId, $input);
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

    protected function writeUpdatedRole(int $roleId, array $input): RoleEntity|array|null
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

        return $this->handleEntityWrite(function () use ($roleId, $entity) {
            return \App::$http->readPutResult('/roles/' . $roleId . '/', $entity)->getEntity();
        });
    }
}
