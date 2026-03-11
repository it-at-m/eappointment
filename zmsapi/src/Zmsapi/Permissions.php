<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsapi\Helper\User as UserHelper;
use BO\Zmsdb\Permission as PermissionRepository;

class Permissions extends BaseController
{
    /**
     * List all atomic permissions.
     *
     * @return string
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        // Only superusers may inspect or edit roles & permissions.
        UserHelper::$request = $request;
        $workstation = UserHelper::readWorkstation(1);
        if (!$workstation->getUseraccount()->isSuperUser()) {
            throw new \BO\Zmsentities\Exception\UserAccountMissingPermissions('Missing superuser permission');
        }

        $repository = new PermissionRepository();
        $rows = $repository->readAll();

        $schemaUrl = 'https://schema.berlin.de/queuemanagement/permission.json';
        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                '$schema' => $schemaUrl,
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
            ];
        }

        $message = Response\Message::create($request);
        $message->data = $data;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, $message->getStatuscode());
    }
}

