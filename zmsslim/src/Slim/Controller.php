<?php

namespace BO\Slim;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class Controller
{
    /**
     * @var \Psr\Container\ContainerInterface|null $containerInterface
     *
     */
    protected ?ContainerInterface $containerInterface = null;

    /**
     * @var \Psr\Http\Message\RequestInterface|null $request
     *
     */
    protected $request = null;

    /**
     * @var \Psr\Http\Message\ResponseInterface|null $response
     *
     */
    protected $response = null;

    /**
     * @param \Psr\Container\ContainerInterface $containerInterface
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
        if (
            $output !== false
            && $output !== ''
            && $output !== '0'
            && !$renderResponse instanceof ResponseInterface
        ) {
            $renderResponse = Render::$response;
            $renderResponse->getBody()->write($output);
        }
        return $renderResponse instanceof ResponseInterface ? $renderResponse : Render::$response;
    }

    // init the request with language translation
    public static function prepareRequest(RequestInterface $request): RequestInterface
    {
        /** @psalm-suppress RedundantCondition Module App subclasses may set MULTILANGUAGE to false. */
        \App::$language = (\App::MULTILANGUAGE) ?
            new \BO\Slim\Language($request, \App::$supportedLanguages) :
            new \BO\Slim\Language($request, array_slice(\App::$supportedLanguages, 0));
        \App::$now = (\App::$now instanceof \DateTimeInterface) ? \App::$now : new \DateTimeImmutable();
        return $request;
    }

    public function initRequest(RequestInterface $request): RequestInterface
    {
        return self::prepareRequest($request);
    }
}
