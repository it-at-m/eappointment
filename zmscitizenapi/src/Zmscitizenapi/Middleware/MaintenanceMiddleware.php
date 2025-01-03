<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Middleware;

use BO\Zmscitizenapi\Localization\ErrorMessages;
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
            $errors[] = ErrorMessages::get('serviceUnavailable');
    
            return ['errors' => $errors, 'statusCode' => self::HTTP_UNAVAILABLE];
        }
        return $next->handle($request);
    }
}