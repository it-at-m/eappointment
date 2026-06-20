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
use BO\Zmsentities\Exception\UserAccountMissingRights;

/**
 * Delete an Useraccount
 */
class UseraccountDelete extends BaseController
{
    /**
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (! $workstation->getUseraccount()->hasPermissions(['useraccount'])) {
            throw new UserAccountMissingRights();
        }

        $loginName = Validator::value($args['loginname'])->isString()->getValue();
        \App::$http->readDeleteResult('/useraccount/' . $loginName . '/')->getEntity();
        return Render::redirect(
            'useraccountList',
            array(),
            array(
                'success' => 'useraccount_deleted'
            )
        );
    }
}
