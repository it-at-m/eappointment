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
        $xAuthKey = static::getXAuthKey();
        $userAccount = (new UserAccount())->readEntityByAuthKey($xAuthKey);
        if (!$userAccount->hasId()) {
            throw new \BO\Zmsentities\Exception\UserAccountMissingLogin();
        }
        if (\App::RIGHTSCHECK_ENABLED) {
            $userAccount->testRights(func_get_args());
        }
        return $userAccount;
    }

    public static function hasRights()
    {
        $xAuthKey = static::getXAuthKey();
        $userAccount = (new UserAccount())->readEntityByAuthKey($xAuthKey);
        return $userAccount->hasId();
    }

    public static function getXAuthKey()
    {
        $request = Render::$request;
        $xAuthKey = $request->getHeader('X-AuthKey');
        if (!$xAuthKey) {
            $cookies = $request->getCookieParams();
            if (array_key_exists('Zmsclient', $cookies)) {
                $xAuthKey = $cookies['Zmsclient'];
            }
        } else {
            $xAuthKey = current($xAuthKey);
        }
        return $xAuthKey;
    }
}
