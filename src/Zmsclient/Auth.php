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
    public static function setKey($authKey)
    {
        $_COOKIE[self::getCookieName()] = $authKey; // for access in the same process
        if (!headers_sent()) {
            setcookie(self::getCookieName(), $authKey, 0, '/', null, true, true);
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
        return false;
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
}