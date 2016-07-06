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
        $userAccount = (new UserAccount())
            ->readEntityByAuthKey(current($xAuthKey))
            ->testRights(func_get_args());
        return $userAccount;
    }

    public static function getStatus($entity, $loginRequired = false)
    {
        $xAuthKey = Render::$request->getHeader('X-AuthKey');
        if (!current($xAuthKey) && !$entity->hasId()) {
            return ($loginRequired) ? 401 : 404;
        }
        return 200;
    }
}
