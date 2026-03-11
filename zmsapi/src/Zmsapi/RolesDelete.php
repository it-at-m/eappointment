<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsapi\Helper\User as UserHelper;
use BO\Zmsdb\Role as RoleRepository;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RolesDelete extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        // Only superusers may delete roles.
        UserHelper::$request = $request;
        $workstation = UserHelper::readWorkstation(1);
        if (!$workstation->getUseraccount()->isSuperUser()) {
            throw new \BO\Zmsentities\Exception\UserAccountMissingPermissions('Missing superuser permission');
        }

        $roleId = isset($args['id']) ? (int) $args['id'] : 0;

        $repository = new RoleRepository();
        $repository->deleteRoleById($roleId);

        $message = Response\Message::create($request);
        $message->data = ['id' => $roleId];

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
