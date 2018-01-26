<?php

namespace BO\Zmsapi\Helper;

use \BO\Slim\Render;
use \BO\Zmsdb\Useraccount;

class UserAuth
{
    /**
     * Get useraccount entity by http basic auth or XAuthKey
     *
     * @return array $useraccount
    */
    public static function getUseraccountByAuthMethod($request)
    {
        $useraccount = null;
        $useraccountQuery = new Useraccount();
        $basicAuth = static::getBasicAuth($request);
        $xAuthKey = static::getXAuthKey($request);
        if ($basicAuth && static::testUseraccountExists($basicAuth['username'], $basicAuth['password'])) {
            $useraccount = $useraccountQuery->readEntity($basicAuth['username']);
        } elseif ($xAuthKey) {
            $useraccount = $useraccountQuery->readEntityByAuthKey($xAuthKey);
        }
        return ($useraccount && $useraccount->hasId()) ? $useraccount : null;
    }

    /**
     * Test if useraccount exists in db
     *
     * @return exception $exception
    */
    public static function testUseraccountExists($loginName = false, $password = false)
    {
        $query = new Useraccount();
        if (! $query->readIsUserExisting($loginName, $password)) {
            $exception = new \BO\Zmsapi\Exception\Useraccount\InvalidCredentials();
            $exception->data['password']['messages'] = [
                'Der Nutzername oder das Passwort wurden falsch eingegeben'
            ];
            throw $exception;
        }
        return true;
    }

    /**
     * Get Basic Authorization header content.
     *
     * @return array $authorization
     */
    private static function getBasicAuth($request)
    {
        $header = $request->getHeaderLine('Authorization');
        if (strpos($header, 'Basic') !== 0) {
            return false;
        }
        $header = explode(':', base64_decode(substr($header, 6)), 2);
        $authorization = [
            'username' => $header[0],
            'password' => isset($header[1]) ? $header[1] : null,
        ];
        $userInfo = explode(':', $request->getUri()->getUserInfo());
        $userInfo = [
            'username' => $userInfo[0],
            'password' => isset($userInfo[1]) ? $userInfo[1] : null
        ];
        return (! $authorization || $authorization['password'] !== $userInfo['password']) ? false : $authorization;
    }

    /**
     * Get XAuthKey from header
     *
     * @return array $useraccount
    */
    private static function getXAuthKey($request)
    {
        $xAuthKey = $request->getHeaderLine('X-AuthKey');
        if (! $xAuthKey) {
            $cookies = $request->getCookieParams();
            $xAuthKey = (array_key_exists('Zmsclient', $cookies)) ? $cookies['Zmsclient'] : null;
        }
        return ($xAuthKey) ? $xAuthKey : false;
    }
}
