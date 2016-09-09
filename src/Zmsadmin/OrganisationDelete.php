<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;

/**
 * Delete an Organisation
 */
class OrganisationDelete extends BaseController
{

    /**
     *
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
    
        $entityId = Validator::value($args['id'])->isNumber()
            ->getValue();
        \App::$http->readDeleteResult('/organisation/' . $entityId . '/')
            ->getEntity();
        return Helper\Render::redirect(
            'owner_overview',
            array (),
            array (
                'success' => 'organisation_deleted'
            )
        );
    }
}
