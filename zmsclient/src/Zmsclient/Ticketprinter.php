<?php

namespace BO\Zmsclient;

/**
 * Session handler for mysql
 */
class Ticketprinter
{
    const string HASH_COOKIE_NAME = 'Ticketprinter';
    const string HOME_URL_COOKIE_NAME = 'Ticketprinter_Homeurl';

    /**
     * @SuppressWarnings(Superglobals)
     *
     * @param string $hash
     * @param \Psr\Http\Message\RequestInterface $request
     * @psalm-suppress DeprecatedMethod
     */
    public static function setHash(string $hash, \Psr\Http\Message\RequestInterface $request): void
    {
        $_COOKIE[self::HASH_COOKIE_NAME] = $hash;
        if (!headers_sent()) {
            $basePath = $request instanceof \BO\Slim\Request ? $request->getBasePath() : '';
            setcookie(
                self::HASH_COOKIE_NAME,
                $hash,
                time() + (60 * 60 * 24 * 365 * 10),
                $basePath,
                '',
                false
            );
        }
    }

    /**
     * @SuppressWarnings(Superglobals)
     *
     * @return false|string
     */
    public static function getHash(): string|false
    {
        if (array_key_exists(self::HASH_COOKIE_NAME, $_COOKIE)) {
            return $_COOKIE[self::HASH_COOKIE_NAME];
        }
        return false;
    }

    /**
     * @SuppressWarnings(Superglobals)
     *
     * @getBasePath () see https://www.slimframework.com/docs/v3/objects/request.html#the-request-method
     * @psalm-suppress DeprecatedMethod
     */
    public static function setHomeUrl(string $url, \Psr\Http\Message\RequestInterface $request): void
    {
        $_COOKIE[self::HOME_URL_COOKIE_NAME] = $url;
        if (!headers_sent()) {
            $basePath = $request instanceof \BO\Slim\Request ? $request->getBasePath() : '';
            setcookie(
                self::HOME_URL_COOKIE_NAME,
                $url,
                time() + (60 * 60 * 24 * 365 * 10),
                $basePath,
                '',
                false,
                true
            );
        }
    }

    /**
     * @SuppressWarnings(Superglobals)
     *
     * @return false|string
     */
    public static function getHomeUrl(): string|false
    {
        if (array_key_exists(self::HOME_URL_COOKIE_NAME, $_COOKIE)) {
            return $_COOKIE[self::HOME_URL_COOKIE_NAME];
        }
        return false;
    }
}
