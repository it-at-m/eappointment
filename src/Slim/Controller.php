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
     * @var \Psr\Http\Message\RequestInterface $request;
     *
     */
    protected $request = null;

    /**
     * @var \Psr\Http\Message\ResponseInterface $response;
     *
     */
    protected $response = null;

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
        $request = $this->initRequest($request);
        Render::$request = $request;
        $this->request = $request;
        Render::$response = $response;
        $this->response = $response;
        Render::$container = $this->containerInterface;
        $className = get_class($this);
        ob_start();
        try {
            $renderResponse = call_user_func_array([$className, 'render'], $args);
        } catch (\Exception $exception) {
            ob_end_clean();
            throw $exception;
        }
        $output = ob_get_clean();
        if ($output && !$renderResponse instanceof ResponseInterface) {
            $renderResponse = Render::$response;
            $renderResponse->getBody()->write($output);
        }
        return $renderResponse instanceof ResponseInterface ? $renderResponse : Render::$response;
    }

    // init the request with language translation
    public function initRequest(RequestInterface $request)
    {
        if (\App::MULTILANGUAGE) {
            \App::$language = new \BO\Slim\Language($request, \App::$supportedLanguages);
        }
        \App::$now = (! \App::$now) ? new \DateTimeImmutable() : \App::$now;
        return $request;
    }
}
