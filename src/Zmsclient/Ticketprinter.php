<?php
namespace BO\Zmsclient;

/**
 * Session handler for mysql
 */
class Ticketprinter
{
    const HASH_COOKIE_NAME = 'Ticketprinter';
    const HOME_URL_COOKIE_NAME = 'Ticketprinter_Homeurl';

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function setHash($hash)
    {
        $_COOKIE[self::HASH_COOKIE_NAME] = $hash;
        if (!headers_sent()) {
            setcookie(self::HASH_COOKIE_NAME, $hash, 0, '/', null, true, true);
        }
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function getHash()
    {
        if (array_key_exists(self::HASH_COOKIE_NAME, $_COOKIE)) {
            return $_COOKIE[self::HASH_COOKIE_NAME];
        }
        return false;
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function setHomeUrl($url)
    {
        $_COOKIE[self::HOME_URL_COOKIE_NAME] = $url;
        if (!headers_sent()) {
            setcookie(self::HOME_URL_COOKIE_NAME, $url, 0, '/', null);
        }
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function getHomeUrl()
    {
        if (array_key_exists(self::HOME_URL_COOKIE_NAME, $_COOKIE)) {
            return $_COOKIE[self::HOME_URL_COOKIE_NAME];
        }
        return false;
    }
}
