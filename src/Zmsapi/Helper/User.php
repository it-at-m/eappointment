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
        $status = 200;
        $xAuthKey = Render::$request->getHeader('X-AuthKey');
        if ($loginRequired && !current($xAuthKey)) {
            $status = 401;
        } elseif (!$entity->hasId()) {
            $status = 404;
        }
        return $status;
    }
}
