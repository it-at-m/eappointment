<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim\Middleware;

use BO\Slim\Container;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

class Route
{
    protected Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /** @psalm-api Called by Slim as middleware. */
    public function getInfo(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $routeInstance = $request->getAttribute(RouteContext::ROUTE);
        if ($routeInstance instanceof \Slim\Routing\Route) {
            $this->container['currentRoute'] = $routeInstance->getName();
            $this->container['currentRouteParams'] = $routeInstance->getArguments();
        }

        return $next->handle($request);
    }
}
