<?php

namespace BO\Slim;

use Psr\Http\Message\RequestInterface;

class SlimApp extends \Slim\App
{
    public function urlFor($name, $params = array())
    {
        $routePath = $this->getContainer()->router->pathFor($name, $params);
        return $routePath;
    }
}
