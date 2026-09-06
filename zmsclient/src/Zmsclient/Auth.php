<?php

namespace BO\Zmsclient;

use App;

/**
 * Session handler for mysql
 */
class Auth
{
    private static string $cookieName = 'X-AuthKey';

    /**
     * @SuppressWarnings(Superglobals)
     */
    public static function setKey(string $authKey, int $expires = 0): void
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
     * @SuppressWarnings(Superglobals)
     *
     * @return null|string
     */
    public static function getKey(): string|null
    {
        $cookieName = self::getCookieName();
        if ($cookieName !== '' && array_key_exists($cookieName, $_COOKIE)) {
            return $_COOKIE[$cookieName];
        }
        return null;
    }

    /**
     * @SuppressWarnings(Superglobals)
     */
    public static function removeKey(): void
    {
        $cookieName = self::getCookieName();
        if ($cookieName !== '' && array_key_exists($cookieName, $_COOKIE)) {
            $oldKey = $_COOKIE[$cookieName];
            if (class_exists('App') && isset(App::$log)) {
                $sessionHash = hash('sha256', $oldKey);
                App::$log->info('Auth session removed', [
                    'event' => 'auth_session_removed',
                    'timestamp' => date('c'),
                    'hashed_session_token' => $sessionHash
                ]);
            }
            unset($_COOKIE[$cookieName]);
            if (!headers_sent()) {
                setcookie($cookieName, '', time() - 3600, '/');
            }
        }
    }

    public static function getCookieName(): string
    {
        return self::$cookieName;
    }

    protected static function getOidcName(): string
    {
        return 'OIDC';
    }

    /**
     * @SuppressWarnings(Superglobals)
     */
    public static function setOidcProvider(string $provider): void
    {
        $_COOKIE[self::getOidcName()] = $provider; // for access in the same process
        if (!headers_sent()) {
            setcookie(self::getOidcName(), $provider, 0, '/', '', true, true);
        }
    }

     /**
     * @SuppressWarnings(Superglobals)
     *
     * @return false|string
     */
    public static function getOidcProvider(): string|false
    {
        $cookieName = self::getOidcName();
        if ($cookieName !== '' && array_key_exists($cookieName, $_COOKIE)) {
            return $_COOKIE[$cookieName];
        }
        return false;
    }

    /**
     * @SuppressWarnings(Superglobals)
     */
    public static function removeOidcProvider(): void
    {
        if (array_key_exists(self::getOidcName(), $_COOKIE)) {
            unset($_COOKIE[self::getOidcName()]);
            if (!headers_sent()) {
                setcookie(self::getOidcName(), '', time() - 3600, '/');
            }
        }
    }
}
