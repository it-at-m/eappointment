<?php
namespace BO\Zmsclient;

/**
 * Session handler for mysql
 */
class Auth
{
    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function setKey($authKey, $expires = 0)
    {
        $_COOKIE[self::getCookieName()] = $authKey; // for access in the same process
        if (!headers_sent()) {
            setcookie(self::getCookieName(), $authKey, $expires, '/', null, true, true);
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
            unset($_COOKIE[self::getCookieName()]);
            if (!headers_sent()) {
                setcookie(self::getCookieName(), '', time() - 3600, '/');
            }
        }
    }

    protected static function getCookieName()
    {
        return 'Zmsclient';
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
            setcookie(self::getOidcName(), $provider, 0, '/', null, true, true);
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
