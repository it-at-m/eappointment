<?php

namespace BO\Slim;

use Psr\Container\ContainerInterface;

/**
 * @extends \Slim\App<ContainerInterface|null>
 */
class SlimApp extends \Slim\App
{
    public function urlFor(string $name, array $params = []): string
    {
        return $this->getRouteCollector()->getRouteParser()->urlFor($name, $params);
    }

    /**
     * @SuppressWarnings("PHPMD.Superglobals")
     */
    public function determineBasePath(): void
    {
        $envBasePath = getenv('ZMS_MODULE_BASEPATH');
        $basePath = $envBasePath !== false ? $envBasePath : '';
        if ($basePath === '') {
            $requestUri = $_SERVER['REQUEST_URI'] ?? null;
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? null;
            if (!is_string($requestUri) || !is_string($scriptName)) {
                return;
            }

            while (
                min(strlen($requestUri), strlen($scriptName)) > strlen($basePath)
                && strncmp($requestUri, $scriptName, strlen($basePath) + 1) === 0
            ) {
                $nextPath = substr($requestUri, 0, strlen($basePath) + 1);
                if ($nextPath === false) {
                    break;
                }
                $basePath = $nextPath;
            }
        }

        $this->setBasePath(rtrim($basePath, '/'));
    }
}
