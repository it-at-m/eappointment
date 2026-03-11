<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RolePermissionDelete extends BaseController
{
    /**
     * Superuser-only endpoint to delete a single role.
     *
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();

        if (!$workstation->getUseraccount()->isSuperUser()) {
            return \BO\Slim\Render::withHtml(
                $response->withStatus(403),
                'exception/bo/zmsentities/exception/schemavalidation.twig',
                [
                    'workstation' => $workstation,
                    'exception' => [
                        'template' => 'exception/bo/zmsentities/exception/schemavalidation.twig',
                        'include' => true,
                        'data' => [
                            '_root' => [
                                'messages' => ['Zugriff verweigert: Nur Superuser dürfen Rollen und Berechtigungen verwalten.'],
                            ],
                        ],
                    ],
                ]
            );
        }

        $roleId = Validator::value($args['id'])->isNumber()->getValue();

        \App::$http->readDeleteResult('/roles/' . $roleId . '/');

        return \BO\Slim\Render::redirect('rolePermissionAdmin', [], []);
    }
}
