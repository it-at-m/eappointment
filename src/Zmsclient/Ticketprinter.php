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
     * @getBasePath() see https://www.slimframework.com/docs/v3/objects/request.html#the-request-method
     *
     */
    public static function setHash($hash, $request)
    {
        $_COOKIE[self::HASH_COOKIE_NAME] = $hash;
        // @codeCoverageIgnoreStart
        if (!headers_sent()) {
            setcookie(self::HASH_COOKIE_NAME, $hash, 0, $request->getUri()->getBasePath(), null, true);
        }
        // @codeCoverageIgnoreEnd
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
        // @codeCoverageIgnoreStart
        if (!headers_sent()) {
            setcookie(self::HOME_URL_COOKIE_NAME, $url, 0, $request->getUri()->getBasePath(), null, true, true);
        }
        // @codeCoverageIgnoreEnd
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
