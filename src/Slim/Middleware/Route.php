<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Routing\RouteContext;

class Route
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getInfo($request, RequestHandlerInterface $next): ResponseInterface
    {
        $routeInstance = $request->getAttribute(RouteContext::ROUTE);
        if ($routeInstance instanceof \Slim\Routing\Route) {
            $this->container['currentRoute'] = $routeInstance->getName();
            $this->container['currentRouteParams'] = $routeInstance->getArguments();
        }

        return $next->handle($request);
    }
}
