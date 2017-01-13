<?php

namespace BO\Zmsapi\Helper;

use \BO\Slim\Render;
use \BO\Zmsdb\UserAccount;

/**
 * example class to generate a response
 */
class User
{
    public static $workstation = null;

    public static function readWorkstation()
    {
        if (!static::$workstation) {
            $xAuthKey = static::getXAuthKey();
            $useraccount = (new UserAccount())->readEntityByAuthKey($xAuthKey);
            if ($useraccount->hasId()) {
                static::$workstation = (new \BO\Zmsdb\Workstation())->readEntity($useraccount->id, 1);
            } else {
                static::$workstation = new \BO\Zmsentities\Workstation();
            }
        }
        return static::$workstation;
    }

    public static function checkRights()
    {
        $workstation = static::readWorkstation();
        $userAccount = $workstation->useraccount;
        if (!$userAccount->hasId()) {
            throw new \BO\Zmsentities\Exception\UserAccountMissingLogin();
        }
        if (\App::RIGHTSCHECK_ENABLED) {
            $userAccount->testRights(func_get_args());
        }
        return $workstation;
    }

    public static function hasRights()
    {
        $userAccount = static::readWorkstation()->useraccount;
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
