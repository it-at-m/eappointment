<?php

namespace BO\Zmscitizenapi\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MaintenanceMiddleware
{

    private const HTTP_UNAVAILABLE = 503;
    private const ERROR_UNAVAILABLE = 'serviceUnavailable';

    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $next)
    {
        if (\App::MAINTENANCE_MODE_ENABLED)
        {
            $errors[] = [
                'errorCode' => self::ERROR_UNAVAILABLE,
                'errorMessage' => 'Service Unavailable: The application is under maintenance.',
                'status' => self::HTTP_UNAVAILABLE,
            ];
    
            return ['errors' => $errors, 'status' => self::HTTP_UNAVAILABLE];
        }
        return $next->handle($request);
    }
}