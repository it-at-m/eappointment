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
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        \App::$http->readDeleteResult('/owner/'. $entityId .'/')->getEntity();
        return \BO\Slim\Render::redirect(
            'owner_overview',
            array(),
            array(
                'success' => 'owner_deleted'
            )
        );
    }
}
