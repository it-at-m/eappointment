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
        // @codeCoverageIgnoreStart
        if (!headers_sent()) {
            setcookie(self::getCookieName(), $authKey, 0, '/', null, true, true);
        }
        // @codeCoverageIgnoreEnd
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

    protected static function getCookieName()
    {
        return 'Zmsclient';
    }
}
