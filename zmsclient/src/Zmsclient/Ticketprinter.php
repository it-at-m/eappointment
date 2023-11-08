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
     * @SuppressWarnings(Superglobals)
     * @param string $hash
     * @param \BO\Zmsclient\Psr7\Request $request
     */
    public static function setHash($hash, $request)
    {
        $_COOKIE[self::HASH_COOKIE_NAME] = $hash;
        if (!headers_sent()) {
            setcookie(
                self::HASH_COOKIE_NAME,
                $hash,
                time() + (60*60*24*365*10),
                $request->getBasePath(),
                null,
                false
            );
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
     * @getBasePath() see https://www.slimframework.com/docs/v3/objects/request.html#the-request-method
     *
     */
    public static function setHomeUrl($url, $request)
    {
        $_COOKIE[self::HOME_URL_COOKIE_NAME] = $url;
        if (!headers_sent()) {
            setcookie(
                self::HOME_URL_COOKIE_NAME,
                $url,
                time() + (60*60*24*365*10),
                $request->getBasePath(),
                null,
                false,
                true
            );
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
