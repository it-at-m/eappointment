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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        if (!$workstation->getUseraccount()->hasRights(['useraccount'])) {
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
            $input = RoleInputHelper::readFormInput($request);
            $submitted = $input;
            $validated = RoleInputHelper::validateAndCreateEntity(
                $input,
                function ($data) {
                    return $this->transformValidationErrors($data);
                }
            );
            $result = ($validated instanceof RoleEntity)
                ? $this->writeUpdatedRole($roleId, $validated)
                : $validated;
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

    protected function writeUpdatedRole(int $roleId, RoleEntity $entity): RoleEntity|array|null
    {
        return $this->handleEntityWrite(function () use ($roleId, $entity) {
            return \App::$http->readPostResult('/roles/' . $roleId . '/', $entity)->getEntity();
        });
    }
}
