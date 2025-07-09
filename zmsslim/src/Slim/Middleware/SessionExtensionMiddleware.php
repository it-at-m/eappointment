<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Middleware to extend session timeout for authenticated users
 * Implements sliding session timeout by resetting cookie expiry and database session expiry
 */
class SessionExtensionMiddleware implements MiddlewareInterface
{
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Extend session timeout for authenticated users
        $authKey = \BO\Zmsclient\Auth::getKey();
        if ($authKey) {
            \BO\Zmsclient\Auth::setKey($authKey, time() + \App::SESSION_DURATION);

            // Call zmsapi to extend session expiry in the database
            if (isset(\App::$http)) {
                try {
                    \App::$http->readPostResult('/session/extend', []);
                } catch (\Exception $e) {
                    // Optionally log or ignore
                }
            }

            if (class_exists('App') && isset(\App::$log)) {
                $sessionHash = hash('sha256', $authKey);
                \App::$log->info('Session extended (sliding timeout)', [
                    'event' => 'auth_session_extended',
                    'timestamp' => date('c'),
                    'hashed_session_token' => $sessionHash
                ]);
            }
        }

        return $handler->handle($request);
    }
}
