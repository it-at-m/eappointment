<?php

namespace BO\Zmsapi\Helper;

use \BO\Slim\Render;
use \BO\Zmsdb\UserAccount;

/**
 * example class to generate a response
 */
class User
{
    public static function checkRights()
    {
        $xAuthKey = Render::$request->getHeader('X-AuthKey');
        $userAccount = (new UserAccount())->readEntityByAuthKey(current($xAuthKey));
        if (\App::RIGHTSCHECK_ENABLED) {
            $userAccount->testRights(func_get_args());
        }
        return $userAccount;
    }
}
