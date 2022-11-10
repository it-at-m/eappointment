<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Route
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getInfo($request, RequestHandlerInterface $next): ResponseInterface
    {
        $routeInstance = $request->getAttribute('route');
        if ($routeInstance instanceof \Slim\Routing\Route) {
            $routeName = $routeInstance->getName();
            $routeName = explode('__', $routeName);
            $this->container['currentRoute'] = (isset($routeName[1])) ? $routeName[1] : $routeName[0];
            $this->container['currentRouteParams'] = $routeInstance->getArguments();
        }

        return $next->handle($request);
    }
}
