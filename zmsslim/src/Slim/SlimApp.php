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

    /**
     * @SuppressWarnings("PHPMD.Superglobals")
     *
     * @return void
     */
    public function determineBasePath(): void
    {
        $basePath = getenv('ZMS_MODULE_BASEPATH') !== false ? getenv('ZMS_MODULE_BASEPATH') : '';
        if (empty($basePath)) {
            $serverParams = $_SERVER;

            if (!isset($serverParams['REQUEST_URI']) || !isset($serverParams['SCRIPT_NAME'])) {
                return;
            }

            while (min(strlen($serverParams['REQUEST_URI']), strlen($serverParams['SCRIPT_NAME'])) > strlen($basePath)
                && strncmp($serverParams['REQUEST_URI'], $serverParams['SCRIPT_NAME'], strlen($basePath) + 1) === 0
            ) {
                $basePath = substr($serverParams['REQUEST_URI'], 0, strlen($basePath) + 1);
            }
        }

        $this->setBasePath(rtrim($basePath, '/'));
    }
}
