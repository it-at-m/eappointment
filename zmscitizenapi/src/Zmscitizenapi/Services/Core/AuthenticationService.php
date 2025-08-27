<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Exceptions\InvalidAuthTokenException;
use Psr\Http\Message\RequestInterface;
use BO\Zmscitizenapi\Models\AuthenticatedUser;

abstract class AuthenticationService
{
    public static function getAuthenticatedUser(RequestInterface $request): AuthenticatedUser | null
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader)) {
            return null;
        }
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) throw new InvalidAuthTokenException('authKeyMismatch', 'Invalid JWT payload.');
        $token = $matches[1];
        return AuthenticatedUser::fromJwtPayload($token);
    }
}
