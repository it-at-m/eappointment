<?php
namespace BO\Zmsclient;

/**
 * Session handler for mysql
 */
class Ticketprinter
{
    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function setHash($hash)
    {
        $_COOKIE[self::getCookieName()] = $hash;
        setcookie(self::getCookieName(), $hash, 0, '/', null, true, true);
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function getHash()
    {
        if (array_key_exists(self::getCookieName(), $_COOKIE)) {
            return $_COOKIE[self::getCookieName()];
        }
        return false;
    }

    protected static function getCookieName()
    {
        return 'Ticketprinter';
    }
}
