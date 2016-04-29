<?php
/**
 * @package Slimproject
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Slim;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Controller
{
    /**
     * @var \Interop\Container\ContainerInterface $containerInterface
     *
     */
    protected $containerInterface = null;

    /**
     * @param \Interop\Container\ContainerInterface $containerInterface
     *
     */
    public function __construct(ContainerInterface $containerInterface)
    {
        $this->containerInterface = $containerInterface;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        Render::$request = $request;
        Render::$response = $response;
        Render::$container = $this->containerInterface;
        $className = get_class($this);
        ob_start();
        $renderResponse = call_user_func_array([$className, 'render'], $args);
        $output = ob_get_clean();
        if ($output && !$renderResponse instanceof ResponseInterface) {
            $renderResponse = Render::$response;
            $renderResponse->getBody()->write($output);
        }
        return $renderResponse instanceof ResponseInterface ? $renderResponse : Render::$response;
    }
}
