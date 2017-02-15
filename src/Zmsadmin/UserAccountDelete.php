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
 * Delete an Useraccount
 */
class UserAccountDelete extends BaseController
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

        $loginName = Validator::value($args['loginname'])->isString()->getValue();
        \App::$http->readDeleteResult('/useraccount/' . $loginName . '/')->getEntity();
        return Helper\Render::redirect(
            'useraccount',
            array (),
            array (
                'success' => 'useraccount_deleted'
            )
        );
    }
}
