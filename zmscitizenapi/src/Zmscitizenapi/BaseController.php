<?php

namespace BO\Zmscitizenapi;

use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

abstract class BaseController extends \BO\Slim\Controller
{
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $request = $this->initRequest($request);
        $noCacheResponse = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $this->readResponse($request, $noCacheResponse, $args);
    }

    /**
     * Hook method for handling responses in child controllers.
     * Child classes should override this method to implement their specific response logic.
     *
     * @param RequestInterface $request The HTTP request
     * @param ResponseInterface $response The HTTP response
     * @param array $args Route parameters
     * @return ResponseInterface The modified response
     */
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args): ResponseInterface|null
    {
        return parent::__invoke($request, $response, $args);
    }

    protected function createJsonResponse(ResponseInterface $response, array $content, int $statusCode): ResponseInterface
    {
        $response = $response->withStatus($statusCode)
                             ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($content));
        return $response;
    }
    

}