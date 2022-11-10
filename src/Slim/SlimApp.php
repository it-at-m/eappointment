<?php

namespace BO\Slim;

use Slim\Interfaces\RouteCollectorInterface;
use Slim\Routing\RouteParser;

class SlimApp extends \Slim\App
{
    public function urlFor(string $name, array $params = []): string
    {
        /** @var RouteCollectorInterface $router */
        $router = $this->getContainer()->get('router');
        return $router->getRouteParser()->urlFor($name, $params);
    }
}
