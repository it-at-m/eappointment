<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RolePermissionUpdate extends BaseController
{
    /**
     * Superuser-only endpoint to update roles and their permission bundles.
     *
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();

        // Guard: only allow superusers (permissions.superuser) to access this UI
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

        // Forward the updated role matrix to zmsapi for persistence.
        \App::$http->readPostResult('/roles/', $request->getParsedBody() ?? []);

        // After update, redirect back to the GET view to show current state.
        return \BO\Slim\Render::redirect('rolePermissionAdmin', [], []);
    }
}
