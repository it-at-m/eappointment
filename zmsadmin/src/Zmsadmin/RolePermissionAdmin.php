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

class RolePermissionAdmin extends BaseController
{
    /**
     * Superuser-only UI to inspect roles and their permission bundles.
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
            // Reuse generic forbidden handling: render standard exception template
            $exception = new \BO\Zmsentities\Exception\UserAccountMissingPermissions(
                'Superuser permission required to manage Rollen & Berechtigungen.'
            );

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

        // Handle CRUD actions on POST by forwarding to zmsapi
        if ($request->getMethod() === 'POST') {
            \App::$http->readPostResult('/roles/', $request->getParsedBody() ?? []);
        }

        // Load roles and their permissions via zmsapi.
        // zmsadmin must not talk to zmsdb directly.
        $apiResult = \App::$http->readGetResult('/roles/', []);
        $rolePermissionMatrix = $apiResult->getCollection();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/rolePermissionAdmin.twig',
            [
                'title' => 'Rollen & Berechtigungen',
                'menuActive' => 'rolePermissionAdmin',
                'workstation' => $workstation,
                'rolePermissionMatrix' => $rolePermissionMatrix,
            ]
        );
    }
}

