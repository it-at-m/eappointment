<?php

namespace BO\Zmsclient;

/**
 * Stub for package-level Psalm (zmsclient is not a Composer dep of zmsslim).
 */
class Auth
{
    public static function setKey($authKey, $expires = 0): void
    {
    }

    /** @return mixed */
    public static function getKey()
    {
    }

    public static function removeKey(): void
    {
    }

    public static function setOidcProvider($providerName): void
    {
    }

    /** @return mixed */
    public static function getOidcProvider()
    {
    }

    public static function removeOidcProvider(): void
    {
    }
}

class Exception extends \Exception
{
}

class Http
{
}

class OAuthService
{
    public function __construct(Http $http, string $configSecureToken)
    {
    }

    /** @return \BO\Zmsentities\Config */
    public function readConfig()
    {
    }

    /**
     * @param \BO\Zmsentities\Useraccount $ownerInputData
     * @return mixed
     */
    public function authenticateWorkstation($ownerInputData, ?string $state = null)
    {
    }
}

class SessionHandler implements \SessionHandlerInterface
{
    public function __construct(Http $http)
    {
    }

    public function close(): bool
    {
        return true;
    }

    public function destroy(string $id): bool
    {
        return true;
    }

    public function gc(int $max_lifetime): int|false
    {
        return 0;
    }

    public function open(string $path, string $name): bool
    {
        return true;
    }

    public function read(string $id): string|false
    {
        return '';
    }

    public function write(string $id, string $data): bool
    {
        return true;
    }
}
