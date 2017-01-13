<?php

namespace BO\Zmsapi\Helper;

use \BO\Slim\Render;
use \BO\Zmsdb\UserAccount;

/**
 * example class to generate a response
 */
class User
{
    public static $current = null;

    public static function readUseraccount()
    {
        if (!static::$current) {
            $xAuthKey = static::getXAuthKey();
            static::$current = (new UserAccount())->readEntityByAuthKey($xAuthKey);
        }
        return static::$current;
    }

    public static function checkRights()
    {
        $userAccount = static::readUseraccount();
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
        $userAccount = static::readUseraccount();
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
