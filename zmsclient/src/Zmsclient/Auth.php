<?php

namespace BO\Zmsclient;

use App;

/**
 * Session handler for mysql
 */
class Auth
{
    private static $cookieName = 'X-AuthKey';

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function setKey($authKey, $expires = 0)
    {
        $_COOKIE[self::getCookieName()] = $authKey; // for access in the same process
        if (!headers_sent()) {
            if (class_exists('App') && isset(App::$log)) {
                $sessionHash = hash('sha256', $authKey);
                App::$log->info('Auth session set', [
                    'event' => 'auth_session_set',
                    'timestamp' => date('c'),
                    'hashed_session_token' => $sessionHash,
                    'expires' => date('Y-m-d H:i:s', $expires),
                    'timezone' => date_default_timezone_get()
                ]);
            }
            setcookie(self::getCookieName(), $authKey, $expires, '/', '', true, true);
        }
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function getKey()
    {
        if (array_key_exists(self::getCookieName(), $_COOKIE)) {
            return $_COOKIE[self::getCookieName()];
        }
        return null;
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function removeKey()
    {
        if (array_key_exists(self::getCookieName(), $_COOKIE)) {
            $oldKey = $_COOKIE[self::getCookieName()];
            if (class_exists('App') && isset(App::$log)) {
                $sessionHash = hash('sha256', $oldKey);
                App::$log->info('Auth session removed', [
                    'event' => 'auth_session_removed',
                    'timestamp' => date('c'),
                    'hashed_session_token' => $sessionHash
                ]);
            }
            unset($_COOKIE[self::getCookieName()]);
            if (!headers_sent()) {
                setcookie(self::getCookieName(), '', time() - 3600, '/');
            }
        }
    }

    public static function getCookieName()
    {
        return self::$cookieName;
    }

    protected static function getOidcName()
    {
        return 'OIDC';
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function setOidcProvider($provider)
    {
        $_COOKIE[self::getOidcName()] = $provider; // for access in the same process
        if (!headers_sent()) {
            setcookie(self::getOidcName(), $provider, 0, '/', '', true, true);
        }
    }

     /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function getOidcProvider()
    {
        if (array_key_exists(self::getOidcName(), $_COOKIE)) {
            return $_COOKIE[self::getOidcName()];
        }
        return false;
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function removeOidcProvider()
    {
        if (array_key_exists(self::getOidcName(), $_COOKIE)) {
            unset($_COOKIE[self::getOidcName()]);
            if (!headers_sent()) {
                setcookie(self::getOidcName(), '', time() - 3600, '/');
            }
        }
    }
}
