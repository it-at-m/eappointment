<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Services\Core;

use BO\Zmscitizenbackend\Exceptions\InvalidAuthTokenException;
use Psr\Http\Message\RequestInterface;
use BO\Zmscitizenbackend\Models\AuthenticatedUser;

abstract class AuthenticationService
{
    public static function getAuthenticatedUser(RequestInterface $request): AuthenticatedUser | null
    {
        $authHeader = $request->getHeaderLine('Authorization');
        if (empty($authHeader)) {
            return null;
        }
        if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            throw new InvalidAuthTokenException('authKeyMismatch', 'Invalid JWT payload.');
        }
        $token = $matches[1];
        return AuthenticatedUser::fromJwtPayload($token);
    }
}
