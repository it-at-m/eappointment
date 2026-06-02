<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;

/**
 * Delete an Owner
 *
 */
class OwnerDelete extends BaseController
{
    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (!$workstation->getUseraccount()->hasPermissions(['jurisdiction'])) {
            throw new \BO\Zmsentities\Exception\UserAccountMissingRights();
        }
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        \App::$http->readDeleteResult('/owner/' . $entityId . '/')->getEntity();
        return \BO\Slim\Render::redirect(
            'owner_overview',
            array(),
            array(
                'success' => 'owner_deleted'
            )
        );
    }
}
