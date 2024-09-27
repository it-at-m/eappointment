<?php

namespace BO\Zmscitizenapi\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MaintenanceMiddleware
{
    public function __invoke(ServerRequestInterface $request, RequestHandlerInterface $next)
    {
        if (\App::MAINTENANCE_MODE_ENABLED)
        {

        }
        return $next->handle($request);
    }
}