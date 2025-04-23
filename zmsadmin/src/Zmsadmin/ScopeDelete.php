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
 * Delete a scope
 */
class ScopeDelete extends BaseController
{
    /**
     *
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $entityId = Validator::value($args['id'])->isNumber()
            ->getValue();

            $entity = \App::$http->readGetResult('/scope/' . $entityId . '/')->getEntity();
            $scopeName = $entity->offsetGet('contact')->offsetGet('name');

        \App::$http->readDeleteResult('/scope/' . $entityId . '/')
            ->getEntity();
        return \BO\Slim\Render::redirect(
            'owner_overview',
            array(),
            array(
                'success' => 'scope_deleted',
                'scopeName' => $scopeName
            )
        );
    }
}
