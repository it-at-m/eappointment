<?php

namespace BO\Zmsadmin;

use BO\Zmsadmin\Helper\RoleInputHelper;
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

        $validator = $request->getAttribute('validator');
        $confirmSuccess = $validator->getParameter('success')->isString()->getValue();
        $permissionList = \App::$http->readGetResult('/permissions/', [])->getCollection();

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
                ? $this->writeNewRole($validated)
                : $validated;
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

    protected function writeNewRole(RoleEntity $entity): RoleEntity|array|null
    {
        return $this->handleEntityWrite(function () use ($entity) {
            return \App::$http->readPostResult('/roles/', $entity)->getEntity();
        });
    }
}
