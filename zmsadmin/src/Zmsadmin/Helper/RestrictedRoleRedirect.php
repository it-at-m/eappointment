<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin\Helper;

use BO\Slim\Render;
use BO\Zmsentities\Useraccount;
use Psr\Http\Message\ResponseInterface;

class RestrictedRoleRedirect
{
    public static function create(Useraccount $useraccount): ?ResponseInterface
    {
        if ($useraccount->hasRole('user_admin')) {
            return Render::redirect('useraccountList', [], []);
        }

        if ($useraccount->hasRole('audit_viewer')) {
            return Render::redirect('search', [], ['hideNavigation' => 1]);
        }

        return null;
    }
}
