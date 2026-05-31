<?php

namespace BO\Zmsadmin;

use BO\Zmsentities\Exception\UserAccountMissingRights;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Roles extends BaseController
{
    /**
     * @SuppressWarnings (Param)
     *
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (!$workstation->getUseraccount()->hasPermissions(['superuser'])) {
            throw new UserAccountMissingRights();
        }

        $validator = $request->getAttribute('validator');
        $success = $validator->getParameter('success')->isString()->getValue();
        $error = $validator->getParameter('error')->isString()->getValue();

        $roleList = \App::$http->readGetResult('/roles/', [])->getCollection();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/rolesList.twig',
            [
                'title' => 'Rollen',
                'menuActive' => 'roles',
                'workstation' => $workstation,
                'roleList' => $roleList,
                'success' => $success,
                'error' => $error,
            ]
        );
    }
}
